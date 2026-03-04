<?php

namespace App\Filament\Widgets;

use Filament\Actions\BulkActionGroup;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TodayOrdersList extends TableWidget
{
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Ventas de Hoy';
    protected static ?int $sort = 4;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => \App\Models\Order::query()->whereDate('created_at', today())->latest())
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('number')
                    ->label('Nº Pedido')
                    ->searchable()
                    ->weight('bold'),
                
                \Filament\Tables\Columns\TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->default('Cliente Anónimo'),
                    
                \Filament\Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'kitchen' => 'warning',
                        'ready' => 'info',
                        'served' => 'success',
                        'paid' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                    
                \Filament\Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('EUR')
                    ->sortable()
                    ->weight('bold'),
                    
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('Hora')
                    ->dateTime('H:i')
                    ->sortable(),
            ]);
    }
}
