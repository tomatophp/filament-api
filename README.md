![Screenshot](https://raw.githubusercontent.com/tomatophp/filament-api/master/arts/3x1io-tomato-api.jpg)

# Resource API Generator

[![Latest Stable Version](https://poser.pugx.org/tomatophp/filament-api/version.svg)](https://packagist.org/packages/tomatophp/filament-api)
[![License](https://poser.pugx.org/tomatophp/filament-api/license.svg)](https://packagist.org/packages/tomatophp/filament-api)
[![Downloads](https://poser.pugx.org/tomatophp/filament-api/d/total.svg)](https://packagist.org/packages/tomatophp/filament-api)

Generate JSON APIs from your Filament resources with a single line of code.

## Version Compatibility

| Plugin | Filament | Laravel | PHP |
|--------|----------|---------|-----|
| 1.x    | 3.x      | 10.x / 11.x | 8.1+ |
| 5.x    | 5.x      | 12.x / 13.x | 8.2+ |

## Installation

The APIs list page keeps the generated endpoints in an in-memory SQLite table, so the `pdo_sqlite` PHP extension is required.

```bash
composer require tomatophp/filament-api
php artisan filament-api:install
```

To list the generated endpoints in your panel, register the plugin in `/app/Providers/Filament/AdminPanelProvider.php`:

```php
->plugin(\TomatoPHP\FilamentApi\FilamentAPIPlugin::make())
```

The endpoints use the `auth:sanctum` middleware by default. Install [Laravel Sanctum](https://laravel.com/docs/sanctum) (`php artisan install:api`) or change `default_middleware` in `config/filament-api.php`.

## Screenshots

![APIs Resource](https://raw.githubusercontent.com/tomatophp/filament-api/master/arts/api-resource.png)
![APIs Resource Dark](https://raw.githubusercontent.com/tomatophp/filament-api/master/arts/api-resource-dark.png)

## Usage

Add the trait to your resource pages:

```php
use Filament\Resources\Pages\ListRecords;
use TomatoPHP\FilamentApi\Traits\InteractWithAPI;

class ListPosts extends ListRecords
{
    use InteractWithAPI;
}
```

That's it, the API is available under `/api/{slug}`. Each page type adds its endpoints:

| Page | Endpoints |
|------|-----------|
| `ListRecords` | GET `/api/{slug}` (list, `?search=` and `?page=`), DELETE `/api/{slug}/{id}` |
| `ManageRecords` | all five endpoints |
| `CreateRecord` | POST `/api/{slug}` |
| `EditRecord` | PUT `/api/{slug}/{id}` |
| `ViewRecord` | GET `/api/{slug}/{id}` |

- The list returns the visible columns of the resource table, searches its searchable columns and uses its default sort.
  Relationship columns such as `author.name` are eager loaded and searchable.
- Create and update validate the request with the rules of every field of the resource form (fields inside sections, tabs and grids included)
  and save only the form fields.
- Queries go through the resource `getEloquentQuery()`, so scopes, soft deletes and tenancy filters apply.
- When the model has a policy, the `viewAny`, `view`, `create`, `update` and `delete` abilities are checked for the API user.
- The routes point at a controller, so `php artisan route:cache` works.

Responses look like `{"status": "success", "message": "OK", "data": ...}`; errors return `{"status": "error", "message": "..."}`
with the status code (404, 403, and 422 with an `errors` object for validation).

## Customize your API

Override these methods on the page:

```php
// Return a JSON resource from the list, show, store and update endpoints
public static function getFilamentAPIResource(): ?string
{
    return PostResource::class;
}

// The middleware of the endpoints
public static function getFilamentAPIMiddleware(): array
{
    return ['auth:sanctum'];
}

// The endpoint slug, defaults to the resource slug
public static function getFilamentAPISlug(): ?string
{
    return 'articles';
}

// The form used to validate and save the store and update endpoints
public static function getFilamentAPIForm(Schema $schema): Schema
{
    return static::getResource()::form($schema);
}

// The table used to shape the list endpoint
public static function getFilamentAPITable(Table $table): Table
{
    return static::getResource()::table($table);
}
```

## Publish Assets

```bash
php artisan vendor:publish --tag="filament-api-config"
```

## Testing

```bash
composer test
```

## Other Filament Packages

Checkout our [Awesome TomatoPHP](https://github.com/tomatophp/awesome)
