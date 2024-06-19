<?php

namespace TomatoPHP\FilamentApi\Traits;

use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ManageRecords;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use TomatoPHP\FilamentApi\Facades\FilamentAPI;
use TomatoPHP\FilamentCms\Services\Contracts\Page;

trait InteractWithAPI
{
    public static function registerAPIRoutes(): array
    {
        $page = (new self());
        $pageType = match (get_parent_class($page)){
            ListRecords::class => 'list',
            ManageRecords::class => 'manager',
            CreateRecord::class => 'create',
            EditRecord::class => 'edit',
            ViewRecord::class => 'view',
            default => null
        };

        return FilamentAPI::register(
            (new self()),
            fn(Form $form): Form => app((new self)::getResource())->form($form),
            ($pageType === 'list' || $pageType === 'manager') ?fn(Table $table): Table => app((new self)::getResource())->table($table):null,
            $pageType,
            self::getFilamentAPIResource(),
            self::getFilamentAPIMiddleware(),
            self::getFilamentAPISlug(),
        );
    }

    public static function getFilamentAPIResource(): ?string
    {
        return null;
    }

    public static function getFilamentAPIMiddleware(): array
    {
        return config('filament-api.default_middleware');
    }

    public static function getFilamentAPISlug(): ?string
    {
        return null;
    }
}
