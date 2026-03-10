<?php

namespace App\Filament\Resources\Dishes\Schemas;

use App\Models\MenuCategory;
use App\Models\Restaurant;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DishForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)->schema([
                    Section::make('Información del plato')
                        ->columnSpan(2)
                        ->schema([
                            Grid::make(2)->schema([
                                TextInput::make('name')
                                    ->label('Nombre')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, $set) =>
                                        $set('slug', \Illuminate\Support\Str::slug($state))
                                    ),
                                TextInput::make('slug')
                                    ->label('Slug (URL)')
                                    ->required()
                                    ->maxLength(255),
                            ]),

                            Textarea::make('description')
                                ->label('Descripción')
                                ->rows(3)
                                ->columnSpanFull(),

                            Grid::make(2)->schema([
                                Select::make('restaurant_id')
                                    ->label('Restaurante')
                                    ->relationship('restaurant', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Select::make('menu_category_id')
                                    ->label('Categoría')
                                    ->options(fn ($get) =>
                                        MenuCategory::where('restaurant_id', $get('restaurant_id'))
                                            ->pluck('name', 'id')
                                    )
                                    ->searchable()
                                    ->reactive(),
                                Select::make('modifierGroups')
                                    ->relationship('modifierGroups', 'name')
                                    ->label('Grupos de Opciones (Extras, Punto...)')
                                    ->multiple()
                                    ->preload(),
                            ]),

                            Grid::make(3)->schema([
                                TextInput::make('price')
                                    ->label('Precio (PVP)')
                                    ->required()
                                    ->numeric()
                                    ->prefix('€')
                                    ->step(0.01),
                                TextInput::make('cost')
                                    ->label('Coste real')
                                    ->numeric()
                                    ->prefix('€')
                                    ->step(0.01),
                                TextInput::make('preparation_time_minutes')
                                    ->label('Tiempo prep. (min)')
                                    ->numeric()
                                    ->suffix('min'),
                            ]),

                            Grid::make(2)->schema([
                                Select::make('kitchen_station')
                                    ->label('Estación de cocina')
                                    ->options([
                                        'hot'    => '🔥 Cocina caliente',
                                        'cold'   => '❄️ Cocina fría',
                                        'bar'    => '🍹 Barra',
                                        'bakery' => '🥐 Panadería',
                                    ])
                                    ->default('hot')
                                    ->required(),
                                TextInput::make('sku')
                                    ->label('SKU / Referencia')
                                    ->maxLength(50),
                            ]),

                            TagsInput::make('allergens')
                                ->label('Alérgenos')
                                ->suggestions([
                                    'gluten', 'lactosa', 'huevos', 'pescado', 'crustáceos',
                                    'frutos secos', 'soja', 'apio', 'mostaza', 'sésamo',
                                    'moluscos', 'altramuces', 'sulfitos',
                                ]),

                            TagsInput::make('tags')
                                ->label('Etiquetas')
                                ->suggestions([
                                    'vegetariano', 'vegano', 'sin gluten', 'sin lactosa',
                                    'picante', 'especial', 'oferta', 'nuevo',
                                ]),
                        ]),

                    Section::make('Imagen & Estado')
                        ->columnSpan(1)
                        ->schema([
                            FileUpload::make('image')
                                ->label('Foto del plato')
                                ->image()
                                ->imageEditor()
                                ->directory('dishes')
                                ->maxSize(2048),

                            Toggle::make('is_available')
                                ->label('Disponible')
                                ->default(true)
                                ->helperText('Si está activo aparece en el menú'),

                            Toggle::make('is_featured')
                                ->label('Destacado')
                                ->helperText('Aparece en la sección de destacados'),

                            TextInput::make('sort_order')
                                ->label('Orden')
                                ->numeric()
                                ->default(0),
                        ]),
                ]),
            ]);
    }
}
