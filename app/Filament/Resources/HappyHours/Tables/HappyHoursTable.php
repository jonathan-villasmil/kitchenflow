<?php

namespace App\Filament\Resources\HappyHours\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;

class HappyHoursTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Promoción')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('valid_days')
                    ->label('Días')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ((int) $state) {
                        1 => 'Lun',
                        2 => 'Mar',
                        3 => 'Mié',
                        4 => 'Jue',
                        5 => 'Vie',
                        6 => 'Sáb',
                        0 => 'Dom',
                        default => $state,
                    }),
                TextColumn::make('target_type')
                    ->label('Alcance')
                    ->badge()
                    ->colors([
                        'primary' => 'all',
                        'warning' => 'menu_category',
                        'success' => 'dish',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'all' => 'Toda la carta',
                        'menu_category' => 'Categoría',
                        'dish' => 'Plato',
                        default => $state,
                    }),
                TextColumn::make('discount_percentage')
                    ->label('Descuento')
                    ->numeric()
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('start_time')
                    ->label('Inicio')
                    ->time('H:i')
                    ->sortable(),
                TextColumn::make('end_time')
                    ->label('Fin')
                    ->time('H:i')
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Activa'),
            ])
            ->filters([
                //
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
