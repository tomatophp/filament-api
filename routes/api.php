<?php

use \Illuminate\Support\Facades\Route;


Route::name('filament.api.')->prefix(config('filament-api.api_prefix'))->group(function (){
    $routes = \TomatoPHP\FilamentApi\Facades\FilamentAPI::getRoutes();
    foreach ($routes as $route){
        foreach ($route as $item){
            Route::match([$item['method']], $item['slug'], $item['callback'])
                ->name($item['name'])
                ->middleware($item['middleware']);
        }
    }
});
