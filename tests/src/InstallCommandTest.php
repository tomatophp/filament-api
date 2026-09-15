<?php

use Illuminate\Support\Facades\File;

it('publishes the config file', function () {
    File::delete(config_path('filament-api.php'));

    $this->artisan('filament-api:install')->assertSuccessful();

    expect(File::exists(config_path('filament-api.php')))->toBeTrue();

    File::delete(config_path('filament-api.php'));
});
