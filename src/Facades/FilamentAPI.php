<?php

namespace TomatoPHP\FilamentApi\Facades;

use Illuminate\Support\Facades\Facade;
use TomatoPHP\FilamentApi\Services\FilamentAPIServices;

/**
 * @method static array register(\Filament\Resources\Pages\Page|string $page, ?string $type = null, ?string $resource = null, ?array $middleware = null, ?string $slug = null)
 * @method static array discover()
 * @method static array getRoutes()
 * @method static void routes(array $routes)
 * @method static void flush()
 *
 * @see FilamentAPIServices
 */
class FilamentAPI extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'filament-api';
    }
}
