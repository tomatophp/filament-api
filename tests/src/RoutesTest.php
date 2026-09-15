<?php

use Illuminate\Support\Facades\Route;
use TomatoPHP\FilamentApi\Facades\FilamentAPI;
use TomatoPHP\FilamentApi\Http\Controllers\FilamentAPIController;
use TomatoPHP\FilamentApi\Tests\Resources\CategoryResource\Pages\ManageCategories;
use TomatoPHP\FilamentApi\Tests\Resources\PostResource\Pages\CreatePost;
use TomatoPHP\FilamentApi\Tests\Resources\PostResource\Pages\EditPost;
use TomatoPHP\FilamentApi\Tests\Resources\PostResource\Pages\ListPosts;
use TomatoPHP\FilamentApi\Tests\Resources\PostResource\Pages\ViewPost;

it('registers the endpoints of every page that uses the trait', function (string $name, string $method, string $uri, string $page) {
    $route = Route::getRoutes()->getByName($name);

    expect($route)->not->toBeNull()
        ->and($route->methods())->toContain($method)
        ->and($route->uri())->toBe($uri)
        ->and($route->getAction('filament_api_page'))->toBe($page);
})->with([
    ['filament.api.posts.index', 'GET', 'api/posts', ListPosts::class],
    ['filament.api.posts.destroy', 'DELETE', 'api/posts/{record}', ListPosts::class],
    ['filament.api.posts.store', 'POST', 'api/posts', CreatePost::class],
    ['filament.api.posts.update', 'PUT', 'api/posts/{record}', EditPost::class],
    ['filament.api.posts.show', 'GET', 'api/posts/{record}', ViewPost::class],
    ['filament.api.categories.index', 'GET', 'api/categories', ManageCategories::class],
    ['filament.api.categories.store', 'POST', 'api/categories', ManageCategories::class],
    ['filament.api.categories.update', 'PUT', 'api/categories/{record}', ManageCategories::class],
    ['filament.api.categories.show', 'GET', 'api/categories/{record}', ManageCategories::class],
    ['filament.api.categories.destroy', 'DELETE', 'api/categories/{record}', ManageCategories::class],
]);

it('points the endpoints at a controller so the routes can be cached', function () {
    $route = Route::getRoutes()->getByName('filament.api.posts.index');

    expect($route->getActionName())->toBe(FilamentAPIController::class . '@index');

    $route->prepareForSerialization();

    expect(unserialize(serialize($route))->getAction('filament_api_page'))->toBe(ListPosts::class);
});

it('applies the page middleware', function () {
    expect(Route::getRoutes()->getByName('filament.api.categories.index')->middleware())->toBe(['auth'])
        ->and(Route::getRoutes()->getByName('filament.api.posts.index')->middleware())->toBe([]);
});

it('keeps routes added through the facade', function () {
    FilamentAPI::routes(FilamentAPI::register(ListPosts::class, 'list', null, [], 'articles'));

    expect(collect(FilamentAPI::getRoutes())->pluck('name'))->toContain('articles.index', 'articles.destroy', 'posts.index');
});
