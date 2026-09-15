<?php

namespace TomatoPHP\FilamentApi;

use Filament\Contracts\Plugin;
use Filament\Panel;
use TomatoPHP\FilamentApi\Filament\Resources\ApiResource;

class FilamentAPIPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-api';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            ApiResource::class,
        ]);
    }

    public function boot(Panel $panel): void {}

    public static function make(): static
    {
        return app(static::class);
    }
}
