<?php

namespace TomatoPHP\FilamentApi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Sushi\Sushi;
use TomatoPHP\FilamentApi\Facades\FilamentAPI;

/**
 * In-memory list of the generated endpoints, shown by the APIs resource.
 *
 * @property string $method
 * @property string $slug
 * @property string $name
 * @property string $table
 * @property array<int, string> $middleware
 */
class APIResource extends Model
{
    use Sushi;

    /**
     * @var array<string, string>
     */
    protected $schema = [
        'method' => 'string',
        'slug' => 'string',
        'name' => 'string',
        'table' => 'string',
        'middleware' => 'json',
    ];

    protected $casts = [
        'middleware' => 'array',
    ];

    /**
     * @return array<int, array<string, string>>
     */
    public function getRows(): array
    {
        $prefix = trim((string) config('filament-api.api_prefix'), '/');

        return array_map(fn (array $route): array => [
            'method' => Str::upper($route['method']),
            'slug' => ltrim("{$prefix}/{$route['slug']}", '/'),
            'name' => "filament.api.{$route['name']}",
            'table' => $route['table'],
            'middleware' => json_encode(array_values($route['middleware'] ?? [])),
        ], FilamentAPI::getRoutes());
    }
}
