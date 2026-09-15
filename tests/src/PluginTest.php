<?php

use Filament\Facades\Filament;
use TomatoPHP\FilamentApi\Filament\Resources\ApiResource;
use TomatoPHP\FilamentApi\Filament\Resources\ApiResource\Pages\ManageAPIResource;
use TomatoPHP\FilamentApi\FilamentAPIPlugin;
use TomatoPHP\FilamentApi\Models\APIResource as Endpoint;
use TomatoPHP\FilamentApi\Tests\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    actingAs(User::create([
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
    ]));
});

it('registers the plugin and the APIs resource on the panel', function () {
    $panel = Filament::getPanel('admin');

    expect($panel->getPlugin('filament-api'))->toBeInstanceOf(FilamentAPIPlugin::class)
        ->and($panel->getResources())->toContain(ApiResource::class);
});

it('renders the APIs page', function () {
    $this->get(ApiResource::getUrl('index'))->assertOk();
});

it('lists every generated endpoint on the APIs page', function () {
    expect(Endpoint::query()->pluck('name')->all())->toContain(
        'filament.api.posts.index',
        'filament.api.posts.destroy',
        'filament.api.posts.store',
        'filament.api.posts.update',
        'filament.api.posts.show',
        'filament.api.categories.index',
    );

    livewire(ManageAPIResource::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords(Endpoint::query()->get())
        ->assertSee('api/posts/{record}');
});
