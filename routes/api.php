<?php

use Illuminate\Support\Facades\Route;
use TomatoPHP\FilamentApi\Facades\FilamentAPI;
use TomatoPHP\FilamentApi\Http\Controllers\FilamentAPIController;

Route::name('filament.api.')->prefix(config('filament-api.api_prefix'))->group(function (): void {
    foreach (FilamentAPI::getRoutes() as $route) {
        Route::match([$route['method']], $route['slug'], [
            'uses' => FilamentAPIController::class . '@' . $route['action'],
            'as' => $route['name'],
            'middleware' => $route['middleware'],
            'filament_api_page' => $route['page'],
            'filament_api_resource' => $route['resource'],
        ]);
    }
});
