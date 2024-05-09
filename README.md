![Screenshot](https://github.com/tomatophp/filament-api/blob/master/arts/3x1io-tomato-api.jpg)

# Resource API Generator

[![Latest Stable Version](https://poser.pugx.org/tomatophp/filament-api/version.svg)](https://packagist.org/packages/tomatophp/filament-api)
[![PHP Version Require](http://poser.pugx.org/tomatophp/filament-api/require/php)](https://packagist.org/packages/tomatophp/filament-api)
[![License](https://poser.pugx.org/tomatophp/filament-api/license.svg)](https://packagist.org/packages/tomatophp/filament-api)
[![Downloads](https://poser.pugx.org/tomatophp/filament-api/d/total.svg)](https://packagist.org/packages/tomatophp/filament-api)

Generate APIs from your filament resource

## Installation

```bash
composer require tomatophp/filament-api
```
after install your package please run this command

```bash
php artisan filament-api:install
```

finally register the plugin on `/app/Providers/Filament/AdminPanelProvider.php`

```php
->plugin(\TomatoPHP\FilamentApi\FilamentAPIPlugin::make())
```

## Publish Assets

you can publish config file by use this command

```bash
php artisan vendor:publish --tag="filament-api-config"
```

you can publish views file by use this command

```bash
php artisan vendor:publish --tag="filament-api-views"
```

you can publish languages file by use this command

```bash
php artisan vendor:publish --tag="filament-api-lang"
```

you can publish migrations file by use this command

```bash
php artisan vendor:publish --tag="filament-api-migrations"
```

## Support

you can join our discord server to get support [TomatoPHP](https://discord.gg/Xqmt35Uh)

## Docs

you can check docs of this package on [Docs](https://docs.tomatophp.com/plugins/laravel-package-generator)

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security

Please see [SECURITY](SECURITY.md) for more information about security.

## Credits

- [Fady Mondy](mailto:info@3x1.io)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
