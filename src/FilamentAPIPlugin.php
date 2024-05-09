<?php

namespace TomatoPHP\FilamentApi;

use Filament\Contracts\Plugin;
use Filament\Panel;


class FilamentAPIPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-api';
    }

    public function register(Panel $panel): void
    {
        //
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return new static();
    }
}
