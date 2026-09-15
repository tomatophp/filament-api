<?php

namespace TomatoPHP\FilamentApi\Tests\Resources\PostResource\Pages;

use Filament\Resources\Pages\EditRecord;
use TomatoPHP\FilamentApi\Tests\Resources\PostResource;
use TomatoPHP\FilamentApi\Traits\InteractWithAPI;

class EditPost extends EditRecord
{
    use InteractWithAPI;

    protected static string $resource = PostResource::class;
}
