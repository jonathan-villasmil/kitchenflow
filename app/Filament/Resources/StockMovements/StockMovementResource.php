<?php

namespace App\Filament\Resources\StockMovements;

use App\Filament\Resources\StockMovements\Pages\CreateStockMovement;
use App\Filament\Resources\StockMovements\Pages\ListStockMovements;
use App\Models\StockMovement;
use App\Models\InventoryItem;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;
    protected static ?string $recordTitleAttribute = 'id';

    public static function getNavigationGroup(): ?string { return 'Inventario'; }
    public static function getModelLabel(): string { return 'Movimiento de Stock'; }
    public static function getPluralModelLabel(): string { return 'Movimientos de Stock'; }

    public static function form(Schema $form): Schema
    {
        return $form
            ->components([
                \Filament\Schemas\Components\Section::make('Detalles del Movimiento')
                    ->schema([
                        Forms\Components\Hidden::make('restaurant_id')
                            ->default(fn () => auth()->user()->restaurant_id ?? 1),

                        Forms\Components\Hidden::make('user_id')
                            ->default(fn () => auth()->id()),

                        Forms\Components\Select::make('type')
                            ->label('Tipo de Movimiento')
                            ->options([
                                'purchase' => '🟢 Compra (Entrada)',
                                'adjustment' => '🔵 Ajuste (Manual)',
                                'transfer' => '🟣 Transferencia',
                                'waste' => '🔴 Merma / Desperdicio (Salida)',
                                'sale' => '🔴 Venta (Salida Automática)',
                            ])
                            ->required()
                            ->live(),

                        Forms\Components\Select::make('inventory_item_id')
                            ->relationship('item', 'name')
                            ->label('Artículo')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(fn () => request()->query('item_id')),

                        Forms\Components\TextInput::make('quantity')
                            ->label('Cantidad Mover')
                            ->numeric()
                            ->required()
                            ->step(0.001)
                            ->helperText('Usar valores positivos. El sistema restará automáticamente si es salida.'),

                        Forms\Components\Select::make('supplier_id')
                            ->relationship('supplier', 'name')
                            ->label('Proveedor')
                            ->searchable()
                            ->preload()
                            ->visible(fn ($get) => $get('type') === 'purchase'),

                        Forms\Components\TextInput::make('unit_cost')
                            ->label('Costo Unitario (€)')
                            ->numeric()
                            ->step(0.001)
                            ->visible(fn ($get) => $get('type') === 'purchase'),

                        Forms\Components\Textarea::make('notes')
                            ->label('Motivo o Notas')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'purchase' => 'success',
                        'sale', 'waste' => 'danger',
                        'adjustment', 'transfer' => 'info',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'purchase' => 'Compra',
                        'sale' => 'Venta',
                        'waste' => 'Merma',
                        'adjustment' => 'Ajuste',
                        'transfer' => 'Transfer',
                    }),

                Tables\Columns\TextColumn::make('item.name')
                    ->label('Artículo')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Cantidad')
                    ->numeric(3)
                    ->color(fn (StockMovement $record): string => in_array($record->type, ['sale', 'waste']) ? 'danger' : 'success')
                    ->formatStateUsing(fn (string $state, StockMovement $record): string => 
                        (in_array($record->type, ['sale', 'waste']) ? '-' : '+') . $state . ' ' . ($record->item->unit ?? '')
                    ),

                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Proveedor')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'purchase' => 'Compras',
                        'adjustment' => 'Ajustes',
                        'waste' => 'Mermas',
                        'sale' => 'Ventas',
                    ])
                    ->label('Tipo'),
                Tables\Filters\SelectFilter::make('inventory_item_id')
                    ->relationship('item', 'name')
                    ->label('Artículo')
                    ->searchable(),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // Movimientos suelen ser inmutables por auditoría, no permitir DeleteAction
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStockMovements::route('/'),
            'create' => CreateStockMovement::route('/create'),
        ];
    }
}
