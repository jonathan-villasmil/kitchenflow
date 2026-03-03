<?php

namespace App\Filament\Resources\Tables\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TableForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('restaurant_id')
                    ->relationship('restaurant', 'name')
                    ->required(),
                Select::make('zone_id')
                    ->relationship('zone', 'name'),
                TextInput::make('number')
                    ->required(),
                TextInput::make('capacity')
                    ->required()
                    ->numeric()
                    ->default(4),
                TextInput::make('pos_x')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('pos_y')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('width')
                    ->required()
                    ->numeric()
                    ->default(80.0),
                TextInput::make('height')
                    ->required()
                    ->numeric()
                    ->default(80.0),
                TextInput::make('shape')
                    ->required()
                    ->default('rectangle'),
                Select::make('status')
                    ->options([
            'available' => 'Available',
            'occupied' => 'Occupied',
            'reserved' => 'Reserved',
            'cleaning' => 'Cleaning',
            'inactive' => 'Inactive',
        ])
                    ->default('available')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
