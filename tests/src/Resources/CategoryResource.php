<?php

namespace TomatoPHP\FilamentApi\Tests\Resources;

use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use TomatoPHP\FilamentApi\Tests\Models\Category;
use TomatoPHP\FilamentApi\Tests\Resources\CategoryResource\Pages\ManageCategories;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->unique(ignoreRecord: true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable(),
            TextColumn::make('posts.title'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCategories::route('/'),
        ];
    }
}
