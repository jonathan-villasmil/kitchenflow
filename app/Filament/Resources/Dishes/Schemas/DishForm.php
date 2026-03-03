<?php

namespace App\Filament\Resources\Dishes\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DishForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('restaurant_id')
                    ->relationship('restaurant', 'name')
                    ->required(),
                TextInput::make('menu_category_id')
                    ->numeric(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('cost')
                    ->numeric()
                    ->prefix('$'),
                FileUpload::make('image')
                    ->image(),
                TextInput::make('sku')
                    ->label('SKU'),
                TextInput::make('allergens'),
                TextInput::make('tags'),
                Toggle::make('is_available')
                    ->required(),
                Toggle::make('is_featured')
                    ->required(),
                TextInput::make('preparation_time_minutes'),
                Select::make('kitchen_station')
                    ->options(['hot' => 'Hot', 'cold' => 'Cold', 'bar' => 'Bar', 'bakery' => 'Bakery'])
                    ->default('hot')
                    ->required(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
