<?php

namespace App\Filament\Resources\MenuCategories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MenuCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('restaurant_id')
                    ->relationship('restaurant', 'name')
                    ->label('Restaurante')
                    ->required(),
                TextInput::make('name')
                    ->label('Nombre')
                    ->required(),
                FileUpload::make('image')
                    ->label('Imagen (Opcional)')
                    ->image()
                    ->directory('menu-categories'),
                TextInput::make('icon')
                    ->label('Icono (Emoji)'),
                TextInput::make('color')
                    ->label('Color HTML'),
                TextInput::make('sort_order')
                    ->label('Orden')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true)
                    ->required(),
            ]);
    }
}
