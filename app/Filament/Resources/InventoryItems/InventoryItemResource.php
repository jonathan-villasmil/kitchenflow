<?php

namespace App\Filament\Resources\InventoryItems;

use App\Filament\Resources\InventoryItems\Pages\CreateInventoryItem;
use App\Filament\Resources\InventoryItems\Pages\EditInventoryItem;
use App\Filament\Resources\InventoryItems\Pages\ListInventoryItems;
use App\Filament\Resources\StockMovements\StockMovementResource;
use App\Models\InventoryItem;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InventoryItemResource extends Resource
{
    protected static ?string $model = InventoryItem::class;
    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string { return 'Inventario'; }
    public static function getModelLabel(): string { return 'Artículo de Inventario'; }
    public static function getPluralModelLabel(): string { return 'Artículos de Inventario'; }

    public static function form(Schema $form): Schema
    {
        return $form
            ->components([
                \Filament\Schemas\Components\Section::make('Detalles del Artículo')
                    ->schema([
                        Forms\Components\Hidden::make('restaurant_id')
                            ->default(fn () => auth()->user()->restaurant_id ?? 1),
                        
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre del Artículo')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('inventory_category_id')
                            ->relationship('category', 'name')
                            ->label('Categoría')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')->required(),
                            ]),

                        Forms\Components\Select::make('unit')
                            ->label('Unidad de Medida')
                            ->options([
                                'kg' => 'Kilogramos (kg)',
                                'g' => 'Gramos (g)',
                                'l' => 'Litros (l)',
                                'ml' => 'Mililitros (ml)',
                                'ud' => 'Unidades (ud)',
                            ])
                            ->required()
                            ->default('kg'),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Control de Stock')
                    ->schema([
                        Forms\Components\TextInput::make('stock_current')
                            ->label('Stock Actual')
                            ->numeric()
                            ->default(0)
                            ->step(0.001)
                            ->disabledOn('edit') // Should be updated via movements
                            ->helperText('Agrega movimientos de stock para modificar el inventario posteriormente.'),
                        
                        Forms\Components\TextInput::make('cost_per_unit')
                            ->label('Costo por Unidad (€)')
                            ->numeric()
                            ->default(0)
                            ->step(0.001)
                            ->prefix('€'),

                        Forms\Components\TextInput::make('stock_minimum')
                            ->label('Stock Mínimo (Alerta)')
                            ->numeric()
                            ->default(0)
                            ->step(0.001),

                        Forms\Components\TextInput::make('stock_maximum')
                            ->label('Stock Máximo')
                            ->numeric()
                            ->step(0.001),
                            
                        Forms\Components\Toggle::make('track_stock')
                            ->label('Controlar Stock')
                            ->default(true)
                            ->helperText('Desactiva si es un artículo que siempre hay disponible sin control estricto.'),
                            
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Artículo')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría')
                    ->sortable()
                    ->badge(),

                Tables\Columns\TextColumn::make('stock_current')
                    ->label('Stock Actual')
                    ->numeric(3)
                    ->sortable()
                    ->color(fn (InventoryItem $record): string => 
                        $record->stock_current <= $record->stock_minimum ? 'danger' : 'success'
                    )
                    ->description(fn (InventoryItem $record): string => $record->unit),

                Tables\Columns\TextColumn::make('cost_per_unit')
                    ->label('Costo U.')
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\IconColumn::make('track_stock')
                    ->label('Rastreado')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('inventory_category_id')
                    ->relationship('category', 'name')
                    ->label('Categoría'),
                    
                Tables\Filters\Filter::make('low_stock')
                    ->label('Bajo Stock')
                    ->query(fn (Builder $query): Builder => $query->whereColumn('stock_current', '<=', 'stock_minimum')),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\Action::make('Ajustar Stock')
                    ->icon('heroicon-o-arrows-up-down')
                    ->color('warning')
                    ->url(fn (InventoryItem $record): string => StockMovementResource::getUrl('create', ['item_id' => $record->id])),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInventoryItems::route('/'),
            'create' => CreateInventoryItem::route('/create'),
            'edit' => EditInventoryItem::route('/{record}/edit'),
        ];
    }
}
