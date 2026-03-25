<?php

namespace App\Filament\Resources;

use App\Models\Restaurant;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RestaurantResource extends Resource
{
    protected static ?string $model = Restaurant::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';
    public static function getNavigationGroup(): ?string { return 'Configuración'; }
    public static function getModelLabel(): string { return 'Restaurante'; }
    public static function getPluralModelLabel(): string { return 'Configuración'; }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Información General')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre del Restaurante')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email de Contacto')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Teléfono')
                            ->tel()
                            ->maxLength(255),
                        TextInput::make('address')
                            ->label('Dirección')
                            ->maxLength(255),
                    ])->columns(2),

                Section::make('Apariencia y Finanzas')
                    ->schema([
                        FileUpload::make('logo')
                            ->label('Logotipo')
                            ->image()
                            ->directory('restaurants'),
                        TextInput::make('tax_rate')
                            ->label('Tasa de Impuesto (IVA/Tax %)')
                            ->numeric()
                            ->default(10.00)
                            ->suffix('%')
                            ->required(),
                        Select::make('currency')
                            ->label('Moneda')
                            ->options([
                                'EUR' => '€ Euro',
                                'USD' => '$ Dólar',
                                'MXN' => '$ Peso Mexicano',
                            ])
                            ->default('EUR')
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo'),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tax_rate')
                    ->label('IVA')
                    ->suffix('%'),
                TextColumn::make('currency')
                    ->label('Moneda'),
                TextColumn::make('updated_at')
                    ->label('Última Actualización')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Restaurants\Pages\ListRestaurants::route('/'),
            'create' => \App\Filament\Resources\Restaurants\Pages\CreateRestaurant::route('/create'),
            'edit' => \App\Filament\Resources\Restaurants\Pages\EditRestaurant::route('/{record}/edit'),
        ];
    }
}
