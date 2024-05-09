<?php

namespace TomatoPHP\FilamentApi;

use Illuminate\Support\ServiceProvider;


class FilamentApiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //Register generate command
        $this->commands([
           \TomatoPHP\FilamentApi\Console\FilamentApiInstall::class,
        ]);

        //Register Config file
        $this->mergeConfigFrom(__DIR__.'/../config/filament-api.php', 'filament-api');

        //Publish Config
        $this->publishes([
           __DIR__.'/../config/filament-api.php' => config_path('filament-api.php'),
        ], 'filament-api-config');

        //Register Migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        //Publish Migrations
        $this->publishes([
           __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'filament-api-migrations');
        //Register views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'filament-api');

        //Publish Views
        $this->publishes([
           __DIR__.'/../resources/views' => resource_path('views/vendor/filament-api'),
        ], 'filament-api-views');

        //Register Langs
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'filament-api');

        //Publish Lang
        $this->publishes([
           __DIR__.'/../resources/lang' => base_path('lang/vendor/filament-api'),
        ], 'filament-api-lang');

        //Register Routes
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

    }

    public function boot(): void
    {
        //you boot methods here
    }
}
