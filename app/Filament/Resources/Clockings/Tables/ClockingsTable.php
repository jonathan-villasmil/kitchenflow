<?php

namespace App\Filament\Resources\Clockings\Tables;

use App\Filament\Resources\Concerns\RestaurantFormScoping;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ClockingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('employee.name')
                    ->label('Empleado')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('clocked_in_at')
                    ->label('Entrada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('clocked_out_at')
                    ->label('Salida')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('total_minutes')
                    ->label('Minutos Totales')
                    ->numeric(2)
                    ->suffix(' min')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha Registro')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('employee_id')
                    ->label('Filtrar por Empleado')
                    ->relationship('employee', 'first_name',
                        modifyQueryUsing: fn (Builder $query) => RestaurantFormScoping::scopeToRestaurant($query)
                    ),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
