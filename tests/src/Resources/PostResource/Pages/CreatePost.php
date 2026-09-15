<?php

namespace TomatoPHP\FilamentApi\Tests\Resources\PostResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use TomatoPHP\FilamentApi\Tests\Resources\PostResource;
use TomatoPHP\FilamentApi\Traits\InteractWithAPI;

class CreatePost extends CreateRecord
{
    use InteractWithAPI;

    protected static string $resource = PostResource::class;
}
