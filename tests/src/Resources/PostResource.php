<?php

namespace TomatoPHP\FilamentApi\Tests\Resources;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use TomatoPHP\FilamentApi\Tests\Models\Post;
use TomatoPHP\FilamentApi\Tests\Resources\PostResource\Pages\CreatePost;
use TomatoPHP\FilamentApi\Tests\Resources\PostResource\Pages\EditPost;
use TomatoPHP\FilamentApi\Tests\Resources\PostResource\Pages\ListPosts;
use TomatoPHP\FilamentApi\Tests\Resources\PostResource\Pages\ViewPost;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                TextInput::make('title')->required()->maxLength(255),
                Textarea::make('body'),
                Select::make('category_id')->relationship('category', 'name')->required(),
                Select::make('user_id')->relationship('author', 'name'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('author.name')->searchable(),
                TextColumn::make('category.name'),
                TextColumn::make('status')->badge(),
                TextColumn::make('secret')->hidden(),
            ])
            ->defaultSort('id', 'desc');
    }

    /**
     * Archived posts never leave the panel, and must not leave the API either.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('status', '!=', 'archived');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosts::route('/'),
            'create' => CreatePost::route('/create'),
            'view' => ViewPost::route('/{record}'),
            'edit' => EditPost::route('/{record}/edit'),
        ];
    }
}
