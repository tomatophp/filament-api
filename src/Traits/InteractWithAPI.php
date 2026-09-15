<?php

namespace TomatoPHP\FilamentApi\Traits;

use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Http\Resources\Json\JsonResource;
use TomatoPHP\FilamentApi\Facades\FilamentAPI;
use TomatoPHP\FilamentApi\Services\FilamentAPIServices;

/**
 * Add to a resource page (List, Manage, Create, Edit or View) to expose it as a JSON API.
 */
trait InteractWithAPI
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function registerAPIRoutes(): array
    {
        return FilamentAPI::register(
            static::class,
            FilamentAPIServices::getPageType(static::class),
            static::getFilamentAPIResource(),
            static::getFilamentAPIMiddleware(),
            static::getFilamentAPISlug(),
        );
    }

    /**
     * The form whose fields validate and fill the store and update endpoints.
     */
    public static function getFilamentAPIForm(Schema $schema): Schema
    {
        return static::getResource()::form($schema);
    }

    /**
     * The table whose visible columns, searchable columns and default sort shape the index endpoint.
     */
    public static function getFilamentAPITable(Table $table): Table
    {
        return static::getResource()::table($table);
    }

    /**
     * @return class-string<JsonResource>|null
     */
    public static function getFilamentAPIResource(): ?string
    {
        return null;
    }

    /**
     * @return array<int, string>
     */
    public static function getFilamentAPIMiddleware(): array
    {
        return config('filament-api.default_middleware', []);
    }

    public static function getFilamentAPISlug(): ?string
    {
        return null;
    }
}
