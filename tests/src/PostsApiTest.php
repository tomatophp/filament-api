<?php

use TomatoPHP\FilamentApi\Tests\Models\Category;
use TomatoPHP\FilamentApi\Tests\Models\Post;
use TomatoPHP\FilamentApi\Tests\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

beforeEach(function () {
    $this->author = User::create(['name' => 'Fady Mondy', 'email' => 'fady@example.com', 'password' => bcrypt('password')]);
    $this->other = User::create(['name' => 'Jane Roe', 'email' => 'jane@example.com', 'password' => bcrypt('password')]);
    $this->news = Category::create(['name' => 'News']);

    $this->first = Post::create([
        'title' => 'First post',
        'body' => 'Body of the first post',
        'secret' => 'not for the API',
        'category_id' => $this->news->id,
        'user_id' => $this->author->id,
    ]);

    $this->second = Post::create([
        'title' => 'Second post',
        'category_id' => $this->news->id,
        'user_id' => $this->other->id,
    ]);
});

it('lists records with the visible table columns only', function () {
    getJson('/api/posts')
        ->assertOk()
        ->assertJsonPath('status', 'success')
        ->assertJsonCount(2, 'data.data')
        ->assertJsonPath('data.data.1.title', 'First post')
        ->assertJsonPath('data.data.1.status', 'published')
        ->assertJsonMissingPath('data.data.1.secret')
        ->assertJsonMissingPath('data.data.1.body');
});

it('orders the listing by the table default sort', function () {
    getJson('/api/posts')
        ->assertJsonPath('data.data.0.id', $this->second->id)
        ->assertJsonPath('data.data.1.id', $this->first->id);
});

it('returns relation columns with the listing', function () {
    getJson('/api/posts')
        ->assertOk()
        ->assertJsonPath('data.data.1.author.name', 'Fady Mondy')
        ->assertJsonPath('data.data.1.category.name', 'News')
        ->assertJsonMissingPath('data.data.1.author.email');
});

it('searches relation columns', function () {
    getJson('/api/posts?search=Jane')
        ->assertOk()
        ->assertJsonCount(1, 'data.data')
        ->assertJsonPath('data.data.0.title', 'Second post');
});

it('searches own columns without leaking the search into the next request', function () {
    getJson('/api/posts?search=First')
        ->assertJsonCount(1, 'data.data')
        ->assertJsonPath('data.data.0.title', 'First post');

    getJson('/api/posts')->assertJsonCount(2, 'data.data');
});

it('keeps the resource query scopes', function () {
    $archived = Post::create(['title' => 'Archived post', 'status' => 'archived', 'category_id' => $this->news->id]);

    getJson('/api/posts')->assertJsonCount(2, 'data.data');
    getJson("/api/posts/{$archived->id}")->assertNotFound();
    deleteJson("/api/posts/{$archived->id}")->assertNotFound();

    expect($archived->fresh())->not->toBeNull();
});

it('shows a record through the page JSON resource', function () {
    getJson("/api/posts/{$this->first->id}")
        ->assertOk()
        ->assertJsonPath('data.title', 'First post')
        ->assertJsonPath('data.headline', 'FIRST POST');
});

it('answers 404 for a record that does not exist', function () {
    getJson('/api/posts/999')
        ->assertNotFound()
        ->assertJsonPath('status', 'error');

    putJson('/api/posts/999', ['title' => 'Nope'])->assertNotFound();
});

it('creates a record and writes form fields only', function () {
    postJson('/api/posts', [
        'title' => 'Created from the API',
        'category_id' => $this->news->id,
        'secret' => 'must not be written',
    ])
        ->assertOk()
        ->assertJsonPath('data.title', 'Created from the API');

    $post = Post::query()->where('title', 'Created from the API')->firstOrFail();

    expect($post->category_id)->toBe($this->news->id)
        ->and($post->secret)->toBeNull();
});

it('validates fields nested in form sections', function () {
    postJson('/api/posts', ['body' => 'No title and no category'])
        ->assertUnprocessable()
        ->assertJsonPath('status', 'error')
        ->assertJsonValidationErrors(['title', 'category_id']);

    expect(Post::query()->count())->toBe(2);
});

it('updates a record', function () {
    putJson("/api/posts/{$this->first->id}", [
        'title' => 'Updated title',
        'category_id' => $this->news->id,
    ])
        ->assertOk()
        ->assertJsonPath('data.title', 'Updated title');

    expect($this->first->fresh()->title)->toBe('Updated title');
});

it('deletes a record the resource policy allows', function () {
    actingAs($this->author);

    deleteJson("/api/posts/{$this->first->id}")->assertOk();

    expect($this->first->fresh())->toBeNull();
});

it('refuses to delete a record the resource policy denies', function () {
    actingAs($this->other);

    deleteJson("/api/posts/{$this->first->id}")
        ->assertForbidden()
        ->assertJsonPath('status', 'error');

    expect($this->first->fresh())->not->toBeNull();
});
