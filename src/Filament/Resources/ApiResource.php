<?php

namespace TomatoPHP\FilamentApi\Filament\Resources;

use Filament\Forms\Form;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use TomatoPHP\FilamentApi\Filament\Resources\ApiResource\Pages\ManageAPIResource;

class ApiResource extends Resource
{
    protected static ?string $model = \TomatoPHP\FilamentApi\Models\APIResource::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    public static function getNavigationLabel(): string
    {
        return "APIs";
    }

    public static function getPluralLabel(): ?string
    {
        return "APIs";
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->description(fn(\TomatoPHP\FilamentApi\Models\APIResource $resource) => $resource->slug)
                    ->searchable()
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('method')
                    ->badge()
                    ->description(fn(\TomatoPHP\FilamentApi\Models\APIResource $resource) => implode(', ', json_decode($resource->middleware)))
                    ->color(fn(\TomatoPHP\FilamentApi\Models\APIResource $resource) => match ($resource->method) {
                        'GET' => 'success',
                        'POST' => 'info',
                        'PUT' => 'warning',
                        'PATCH' => 'orange',
                        'DELETE' => 'danger',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('table')
                    ->label('Filter By Table')
                    ->searchable()
                    ->options(\TomatoPHP\FilamentApi\Models\APIResource::query()->groupBy('table')->pluck('table', 'table')->toArray())
            ])
            ->defaultGroup('table');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAPIResource::route('/'),
        ];
    }
}
