<?php

namespace App\Filament\Widgets;

use App\Support\AdminRestaurantContext;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use App\Models\Customer;
use Filament\Tables\Columns\TextColumn;

class TopCustomersWidget extends TableWidget
{
    protected static ?string $heading = 'Mejores Clientes (Fidelización)';
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'half';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AdminRestaurantContext::scope(Customer::query())
                    ->where('loyalty_points', '>', 0)
                    ->orderByDesc('loyalty_points')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Cliente')
                    ->weight('bold'),
                TextColumn::make('loyalty_points')
                    ->label('Puntos')
                    ->badge()
                    ->color('warning')
                    ->sortable(),
                TextColumn::make('orders_count')
                    ->label('Pedidos')
                    ->counts('orders')
                    ->sortable(),
            ]);
    }
}
