<?php

namespace App\Filament\Resources\HappyHours\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use App\Models\MenuCategory;
use App\Models\Dish;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class HappyHourForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    Section::make('Configuración Básica')->columnSpan(1)->schema([
                        Select::make('restaurant_id')
                            ->relationship('restaurant', 'name')
                            ->required()
                            ->default(1),
                        TextInput::make('name')
                            ->label('Nombre de la Promoción')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('discount_percentage')
                            ->label('Descuento (%)')
                            ->numeric()
                            ->required()
                            ->minValue(0.01)
                            ->maxValue(100)
                            ->step(0.01),
                        Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),
                    ]),

                    Section::make('Horario y Aplicación')->columnSpan(1)->schema([
                        Select::make('valid_days')
                            ->label('Días válidos')
                            ->multiple()
                            ->options([
                                1 => 'Lunes',
                                2 => 'Martes',
                                3 => 'Miércoles',
                                4 => 'Jueves',
                                5 => 'Viernes',
                                6 => 'Sábado',
                                0 => 'Domingo',
                            ])
                            ->required(),
                        Grid::make(2)->schema([
                            TimePicker::make('start_time')
                                ->label('Hora Inicio')
                                ->seconds(false)
                                ->required(),
                            TimePicker::make('end_time')
                                ->label('Hora Fin')
                                ->seconds(false)
                                ->required(),
                        ]),
                    ]),

                    Section::make('Alcance del Descuento')->columnSpanFull()->schema([
                        Select::make('target_type')
                            ->label('Aplicar a')
                            ->options([
                                'all' => 'Toda la carta',
                                'menu_category' => 'Una categoría específica',
                                'dish' => 'Un plato específico',
                            ])
                            ->required()
                            ->live(),

                        Select::make('target_id')
                            ->label('Seleccionar Ítem')
                            ->options(function (Get $get) {
                                if ($get('target_type') === 'menu_category') {
                                    return MenuCategory::where('restaurant_id', $get('restaurant_id'))->pluck('name', 'id');
                                }
                                if ($get('target_type') === 'dish') {
                                    return Dish::where('restaurant_id', $get('restaurant_id'))->pluck('name', 'id');
                                }
                                return [];
                            })
                            ->hidden(fn (Get $get) => $get('target_type') === 'all' || blank($get('target_type')))
                            ->required(fn (Get $get) => in_array($get('target_type'), ['menu_category', 'dish']))
                            ->searchable(),
                    ]),
                ]),
            ]);
    }
}
