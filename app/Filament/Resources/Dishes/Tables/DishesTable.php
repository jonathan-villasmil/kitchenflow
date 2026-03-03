<?php

namespace App\Filament\Resources\Dishes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class DishesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('')
                    ->circular()
                    ->size(40),

                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('category.name')
                    ->label('Categoría')
                    ->badge()
                    ->color('info'),

                TextColumn::make('price')
                    ->label('Precio')
                    ->money('EUR')
                    ->sortable(),

                TextColumn::make('cost')
                    ->label('Coste')
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('kitchen_station')
                    ->label('Estación')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'hot'    => '🔥 Caliente',
                        'cold'   => '❄️ Fría',
                        'bar'    => '🍹 Barra',
                        'bakery' => '🥐 Panadería',
                        default  => $state,
                    })
                    ->color(fn ($state) => match($state) {
                        'hot'    => 'danger',
                        'cold'   => 'info',
                        'bar'    => 'warning',
                        'bakery' => 'success',
                        default  => 'gray',
                    }),

                IconColumn::make('is_available')
                    ->label('Disponible')
                    ->boolean(),

                IconColumn::make('is_featured')
                    ->label('Destacado')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('kitchen_station')
                    ->label('Estación')
                    ->options([
                        'hot'    => '🔥 Caliente',
                        'cold'   => '❄️ Fría',
                        'bar'    => '🍹 Barra',
                        'bakery' => '🥐 Panadería',
                    ]),
                TernaryFilter::make('is_available')
                    ->label('Disponibles'),
                TernaryFilter::make('is_featured')
                    ->label('Destacados'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }
}
