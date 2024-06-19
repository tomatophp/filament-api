![Screenshot](https://raw.githubusercontent.com/tomatophp/filament-api/master/arts/3x1io-tomato-api.jpg)

# Resource API Generator

[![Latest Stable Version](https://poser.pugx.org/tomatophp/filament-api/version.svg)](https://packagist.org/packages/tomatophp/filament-api)
[![PHP Version Require](http://poser.pugx.org/tomatophp/filament-api/require/php)](https://packagist.org/packages/tomatophp/filament-api)
[![License](https://poser.pugx.org/tomatophp/filament-api/license.svg)](https://packagist.org/packages/tomatophp/filament-api)
[![Downloads](https://poser.pugx.org/tomatophp/filament-api/d/total.svg)](https://packagist.org/packages/tomatophp/filament-api)

Generate APIs from your filament resource using single line of code

## Installation

```bash
composer require tomatophp/filament-api
```

if you want to use API Resource to list your generated APIs you can register the plugin on `/app/Providers/Filament/AdminPanelProvider.php`

```php
->plugin(\TomatoPHP\FilamentApi\FilamentAPIPlugin::make())
```

## Screenshots

![APIs Resource](https://raw.githubusercontent.com/tomatophp/filament-api/master/arts/api-resource.jpg)

## Usage

you can generate API by add this trait to your resource pages

```php
use TomatoPHP\FilamentApi\Traits\InteractWithAPI;
use \Filament\Resources\Pages\ListRecords;

class ListPosts extends ListRecords
{
    use InteractWithAPI;
}
```

and that's it you can now access your API by `/api/{slug}`

we provide 5 methods:

- GET `/api/{slug}` to list all records `support searching by use search=`
- GET `/api/{slug}/{id}` to get single record
- POST `/api/{slug}` to create new record
- PUT `/api/{slug}/{id}` to update record
- DELETE `/api/{slug}/{id}` to delete record

## Custom your API

you can customize your api by override this methods

```php
// Use to return API JSON Resource on Index/Show/Store/Update
public static function getFilamentAPIResource(): ?string
{
    return null;
}

// Use To Custom Your Route Middleware
public static function getFilamentAPIMiddleware(): array
{
    return config('filament-api.default_middleware');
}

// Use To Change the Endpoint Slug
public static function getFilamentAPISlug(): ?string
{
    return null;
}
```

## Publish Assets

you can publish config file by use this command

```bash
php artisan vendor:publish --tag="filament-api-config"
```

## Support

you can join our discord server to get support [TomatoPHP](https://discord.gg/Xqmt35Uh)

## Docs

you can check docs of this package on [Docs](https://docs.tomatophp.com/filament/filament-api)

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security

Please see [SECURITY](SECURITY.md) for more information about security.

## Credits

- [Fady Mondy](mailto:info@3x1.io)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
