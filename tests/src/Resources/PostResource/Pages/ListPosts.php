<?php

namespace TomatoPHP\FilamentApi\Tests\Resources\PostResource\Pages;

use Filament\Resources\Pages\ListRecords;
use TomatoPHP\FilamentApi\Tests\Resources\PostResource;
use TomatoPHP\FilamentApi\Traits\InteractWithAPI;

class ListPosts extends ListRecords
{
    use InteractWithAPI;

    protected static string $resource = PostResource::class;
}
