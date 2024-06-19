<?php

namespace TomatoPHP\FilamentApi;

use Filament\Facades\Filament;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\ServiceProvider;
use TomatoPHP\FilamentApi\Facades\FilamentAPI;
use TomatoPHP\FilamentApi\Services\FilamentAPIServices;


class FilamentApiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //Register Config file
        $this->mergeConfigFrom(__DIR__.'/../config/filament-api.php', 'filament-api');

        //Publish Config
        $this->publishes([
           __DIR__.'/../config/filament-api.php' => config_path('filament-api.php'),
        ], 'filament-api-config');

        $this->app->bind('filament-api', function(){
            return new FilamentAPIServices();
        });
    }

    public function boot(): void
    {
        $resources = Filament::getResources();
        $routes = [];
        foreach ($resources as $resource){
            $pages = app($resource)->getPages();
            foreach ($pages as $page){
                $page = app($page->getPage());
                if(get_class_methods($page) && in_array('TomatoPHP\FilamentApi\Traits\InteractWithAPI', class_uses($page))){
                    $routes[] = $page::registerAPIRoutes();
                }
            }
        }

        FilamentAPI::routes($routes);

        //Register Routes
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
    }
}
