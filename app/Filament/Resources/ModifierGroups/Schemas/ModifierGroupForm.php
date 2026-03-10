<?php

namespace App\Filament\Resources\ModifierGroups\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Schema;

class ModifierGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('restaurant_id')
                    ->relationship('restaurant', 'name')
                    ->label('Restaurante')
                    ->default(auth()->user()->restaurant_id ?? 1)
                    ->required(),
                TextInput::make('name')
                    ->label('Nombre del Grupo (Ej: Extras, Punto de Carne)')
                    ->required(),
                Toggle::make('is_multiple_choice')
                    ->label('Permitir Selección Múltiple')
                    ->default(true)
                    ->required(),
                Toggle::make('is_required')
                    ->label('Es Obligatorio (El cliente debe elegir uno)')
                    ->default(false)
                    ->required(),
                Repeater::make('modifiers')
                    ->relationship()
                    ->label('Opciones (Modificadores)')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre de la Opción (Ej: Sin Cebolla, Extra Queso)')
                            ->required(),
                        TextInput::make('price')
                            ->label('Precio Extra (€)')
                            ->numeric()
                            ->default(0.00)
                            ->required(),
                    ])
                    ->columns(2)
                    ->defaultItems(1)
                    ->columnSpanFull(),
            ]);
    }
}
