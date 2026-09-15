<?php

namespace TomatoPHP\FilamentApi\Console;

use Illuminate\Console\Command;

class FilamentApiInstall extends Command
{
    protected $signature = 'filament-api:install';

    protected $description = 'Publish the Filament API config file';

    public function handle(): int
    {
        $this->callSilently('vendor:publish', ['--tag' => 'filament-api-config']);

        $this->components->info('Filament API installed successfully.');

        return self::SUCCESS;
    }
}
