<?php

namespace App\Filament\Resources\Tables\Schemas;

use App\Models\Zone;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
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
                Grid::make(3)->schema([
                    Section::make('Información de la mesa')
                        ->columnSpan(2)
                        ->schema([
                            Grid::make(2)->schema([
                                Select::make('restaurant_id')
                                    ->label('Restaurante')
                                    ->relationship('restaurant', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live(),

                                Select::make('zone_id')
                                    ->label('Zona / Sala')
                                    ->options(fn ($get) =>
                                        Zone::where('restaurant_id', $get('restaurant_id'))
                                            ->pluck('name', 'id')
                                    )
                                    ->searchable()
                                    ->placeholder('Sin zona'),
                            ]),

                            Grid::make(3)->schema([
                                TextInput::make('number')
                                    ->label('Número / Nombre')
                                    ->required()
                                    ->placeholder('Ej: 1, 2, Terraza-1'),

                                TextInput::make('capacity')
                                    ->label('Capacidad (personas)')
                                    ->required()
                                    ->numeric()
                                    ->default(4)
                                    ->minValue(1)
                                    ->maxValue(50),

                                Select::make('shape')
                                    ->label('Forma')
                                    ->options([
                                        'rectangle' => '▭ Rectangular',
                                        'circle'    => '⭕ Circular',
                                        'square'    => '□ Cuadrada',
                                    ])
                                    ->default('rectangle')
                                    ->required(),
                            ]),

                            Select::make('status')
                                ->label('Estado')
                                ->options([
                                    'available' => '✅ Disponible',
                                    'occupied'  => '🔴 Ocupada',
                                    'reserved'  => '🟡 Reservada',
                                    'cleaning'  => '🧹 En limpieza',
                                    'inactive'  => '⛔ Inactiva',
                                ])
                                ->default('available')
                                ->required(),
                        ]),

                    Section::make('Posición en plano')
                        ->columnSpan(1)
                        ->description('Coordenadas para el plano visual del restaurante')
                        ->schema([
                            TextInput::make('pos_x')
                                ->label('Posición X')
                                ->numeric()
                                ->default(0)
                                ->step(1),

                            TextInput::make('pos_y')
                                ->label('Posición Y')
                                ->numeric()
                                ->default(0)
                                ->step(1),

                            TextInput::make('width')
                                ->label('Ancho (px)')
                                ->numeric()
                                ->default(80)
                                ->step(10),

                            TextInput::make('height')
                                ->label('Alto (px)')
                                ->numeric()
                                ->default(80)
                                ->step(10),

                            Toggle::make('is_active')
                                ->label('Mesa activa')
                                ->default(true),
                        ]),
                ]),
            ]);
    }
}
