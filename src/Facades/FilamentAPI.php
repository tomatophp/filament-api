<?php

namespace TomatoPHP\FilamentApi\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void register($component, callable $form, callable $table=null, ?string $type=null, ?string $resource=null, ?array $middleware=null, ?string $slug=null)
 * @method static void generate($component, $table, $form)
 * @method static array getRoutes()
 * @method static void routes(array $routes)
 *
 **/
class FilamentAPI extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'filament-api';
    }
}
