<?php

namespace TomatoPHP\FilamentApi;

use Illuminate\Support\ServiceProvider;
use TomatoPHP\FilamentApi\Console\FilamentApiInstall;
use TomatoPHP\FilamentApi\Services\FilamentAPIServices;

class FilamentApiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/filament-api.php', 'filament-api');

        $this->publishes([
            __DIR__ . '/../config/filament-api.php' => config_path('filament-api.php'),
        ], 'filament-api-config');

        $this->commands([
            FilamentApiInstall::class,
        ]);

        $this->app->singleton('filament-api', fn (): FilamentAPIServices => new FilamentAPIServices);
        $this->app->alias('filament-api', FilamentAPIServices::class);
    }

    public function boot(): void
    {
        // Skipped automatically when the routes are cached.
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
    }
}
