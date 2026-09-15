<?php

namespace TomatoPHP\FilamentApi\Tests\Resources\CategoryResource\Pages;

use Filament\Resources\Pages\ManageRecords;
use TomatoPHP\FilamentApi\Tests\Resources\CategoryResource;
use TomatoPHP\FilamentApi\Traits\InteractWithAPI;

class ManageCategories extends ManageRecords
{
    use InteractWithAPI;

    protected static string $resource = CategoryResource::class;

    public static function getFilamentAPIMiddleware(): array
    {
        return ['auth'];
    }
}
