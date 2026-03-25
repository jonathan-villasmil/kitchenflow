<?php

namespace App\Filament\Resources;

use App\Models\Zone;
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

class ZoneResource extends Resource
{
    protected static ?string $model = Zone::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-map';
    public static function getNavigationGroup(): ?string { return 'Restaurante'; }
    public static function getModelLabel(): string { return 'Zona / Sala'; }
    public static function getPluralModelLabel(): string { return 'Zonas y Salas'; }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Información de la Zona')
                    ->schema([
                        Select::make('restaurant_id')
                            ->label('Restaurante')
                            ->relationship('restaurant', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        TextInput::make('name')
                            ->label('Nombre de la Zona')
                            ->required()
                            ->placeholder('Ej: Terraza, Salón Principal, Piso 1')
                            ->maxLength(255),
                        TextInput::make('description')
                            ->label('Descripción (opcional)')
                            ->maxLength(255),
                        FileUpload::make('image')
                            ->label('Plano / Imagen de la zona')
                            ->directory('zones')
                            ->image(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Imagen'),
                TextColumn::make('name')
                    ->label('Zona')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('restaurant.name')
                    ->label('Restaurante')
                    ->sortable(),
                TextColumn::make('tables_count')
                    ->label('Mesas')
                    ->counts('tables'),
            ])
            ->filters([])
            ->actions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Zones\Pages\ListZones::route('/'),
            'create' => \App\Filament\Resources\Zones\Pages\CreateZone::route('/create'),
            'edit' => \App\Filament\Resources\Zones\Pages\EditZone::route('/{record}/edit'),
        ];
    }
}
