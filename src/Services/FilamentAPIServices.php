<?php

namespace TomatoPHP\FilamentApi\Services;

use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ManageRecords;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\ViewRecord;
use TomatoPHP\FilamentApi\Traits\InteractWithAPI;

class FilamentAPIServices
{
    /**
     * The endpoints each kind of resource page exposes.
     *
     * @var array<string, array<int, string>>
     */
    public const ACTIONS = [
        'list' => ['index', 'destroy'],
        'manager' => ['index', 'destroy', 'store', 'update', 'show'],
        'create' => ['store'],
        'edit' => ['update'],
        'view' => ['show'],
    ];

    /**
     * @var array<string, string>
     */
    public const METHODS = [
        'index' => 'get',
        'show' => 'get',
        'store' => 'post',
        'update' => 'put',
        'destroy' => 'delete',
    ];

    /**
     * Routes registered by hand through the facade, keyed by route name.
     *
     * @var array<string, array<string, mixed>>
     */
    protected array $routes = [];

    /**
     * Routes discovered from the resource pages of every panel, keyed by route name.
     *
     * @var array<string, array<string, mixed>>|null
     */
    protected ?array $discovered = null;

    /**
     * Describe the endpoints of a resource page.
     *
     * @param  Page|class-string<Page>  $page
     * @param  array<int, string>|null  $middleware
     * @return array<int, array{table: string, method: string, slug: string, action: string, name: string, middleware: array<int, string>, page: class-string<Page>, resource: ?string}>
     */
    public function register(
        Page | string $page,
        ?string $type = null,
        ?string $resource = null,
        ?array $middleware = null,
        ?string $slug = null,
    ): array {
        $page = is_string($page) ? $page : $page::class;
        $type ??= static::getPageType($page);
        $slug ??= $page::getResource()::getSlug();
        $middleware ??= config('filament-api.default_middleware', []);
        $name = str_replace('/', '.', $slug);

        return array_map(fn (string $action): array => [
            'table' => $slug,
            'method' => static::METHODS[$action],
            'slug' => in_array($action, ['index', 'store'], true) ? $slug : "{$slug}/{record}",
            'action' => $action,
            'name' => "{$name}.{$action}",
            'middleware' => array_values($middleware),
            'page' => $page,
            'resource' => $resource,
        ], static::ACTIONS[$type] ?? []);
    }

    /**
     * Find every resource page that uses the InteractWithAPI trait on every panel.
     *
     * @return array<string, array<string, mixed>>
     */
    public function discover(): array
    {
        $routes = [];

        foreach (Filament::getPanels() as $panel) {
            foreach ($panel->getResources() as $resource) {
                foreach ($resource::getPages() as $registration) {
                    $page = $registration->getPage();

                    if (! in_array(InteractWithAPI::class, class_uses_recursive($page), true)) {
                        continue;
                    }

                    foreach ($page::registerAPIRoutes() as $route) {
                        $routes[$route['name']] = $route;
                    }
                }
            }
        }

        return $routes;
    }

    /**
     * Add routes by hand. Accepts a flat list of routes or a list of route lists.
     *
     * @param  array<int, mixed>  $routes
     */
    public function routes(array $routes): void
    {
        foreach ($routes as $route) {
            if (is_array($route) && array_is_list($route)) {
                $this->routes($route);

                continue;
            }

            $this->routes[$route['name']] = $route;
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRoutes(): array
    {
        $this->discovered ??= $this->discover();

        return array_values([...$this->discovered, ...$this->routes]);
    }

    /**
     * Forget the discovered routes so the next call scans the panels again.
     */
    public function flush(): void
    {
        $this->discovered = null;
    }

    /**
     * @param  class-string<Page>  $page
     */
    public static function getPageType(string $page): ?string
    {
        return match (true) {
            is_a($page, ManageRecords::class, true) => 'manager',
            is_a($page, ListRecords::class, true) => 'list',
            is_a($page, CreateRecord::class, true) => 'create',
            is_a($page, EditRecord::class, true) => 'edit',
            is_a($page, ViewRecord::class, true) => 'view',
            default => null,
        };
    }
}
