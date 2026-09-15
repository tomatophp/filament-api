<?php

namespace TomatoPHP\FilamentApi\Filament\Resources;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use TomatoPHP\FilamentApi\Filament\Resources\ApiResource\Pages\ManageAPIResource;
use TomatoPHP\FilamentApi\Models\APIResource as Endpoint;

class ApiResource extends Resource
{
    protected static ?string $model = Endpoint::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-paper-airplane';

    public static function getNavigationGroup(): ?string
    {
        return __('Settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('APIs');
    }

    public static function getModelLabel(): string
    {
        return __('API');
    }

    public static function getPluralModelLabel(): string
    {
        return __('APIs');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->description(fn (Endpoint $record): string => $record->slug)
                    ->searchable()
                    ->badge()
                    ->sortable(),
                TextColumn::make('method')
                    ->label(__('Method'))
                    ->badge()
                    ->description(fn (Endpoint $record): string => implode(', ', $record->middleware ?? []))
                    ->color(fn (Endpoint $record): string => match ($record->method) {
                        'GET' => 'success',
                        'POST' => 'info',
                        'PUT' => 'warning',
                        'DELETE' => 'danger',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('table')
                    ->label(__('Filter By Table'))
                    ->searchable()
                    ->options(fn (): array => Endpoint::query()->pluck('table', 'table')->all()),
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
