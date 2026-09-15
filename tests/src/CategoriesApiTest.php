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
    $this->user = User::create(['name' => 'Fady Mondy', 'email' => 'fady@example.com', 'password' => bcrypt('password')]);
    $this->news = Category::create(['name' => 'News']);

    Post::create(['title' => 'First post', 'category_id' => $this->news->id]);
    Post::create(['title' => 'Second post', 'category_id' => $this->news->id]);
});

it('requires authentication when the page asks for it', function () {
    getJson('/api/categories')->assertUnauthorized();
});

it('lists records with has-many relation columns', function () {
    actingAs($this->user);

    getJson('/api/categories')
        ->assertOk()
        ->assertJsonPath('data.data.0.name', 'News')
        ->assertJsonCount(2, 'data.data.0.posts')
        ->assertJsonPath('data.data.0.posts.0.title', 'First post');
});

it('shows, creates and deletes through a manage page', function () {
    actingAs($this->user);

    getJson("/api/categories/{$this->news->id}")->assertOk()->assertJsonPath('data.name', 'News');

    postJson('/api/categories', ['name' => 'Releases'])->assertOk()->assertJsonPath('data.name', 'Releases');

    $releases = Category::query()->where('name', 'Releases')->firstOrFail();

    deleteJson("/api/categories/{$releases->id}")->assertOk();

    expect($releases->fresh())->toBeNull();
});

it('ignores the current record on unique rules when updating', function () {
    actingAs($this->user);

    putJson("/api/categories/{$this->news->id}", ['name' => 'News'])->assertOk();

    postJson('/api/categories', ['name' => 'News'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});
