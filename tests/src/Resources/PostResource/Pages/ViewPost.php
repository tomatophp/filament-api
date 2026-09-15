<?php

namespace TomatoPHP\FilamentApi\Tests\Resources\PostResource\Pages;

use Filament\Resources\Pages\ViewRecord;
use TomatoPHP\FilamentApi\Tests\Http\PostJsonResource;
use TomatoPHP\FilamentApi\Tests\Resources\PostResource;
use TomatoPHP\FilamentApi\Traits\InteractWithAPI;

class ViewPost extends ViewRecord
{
    use InteractWithAPI;

    protected static string $resource = PostResource::class;

    public static function getFilamentAPIResource(): ?string
    {
        return PostJsonResource::class;
    }
}
