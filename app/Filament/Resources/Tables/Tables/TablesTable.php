<?php

namespace App\Filament\Resources\Tables\Tables;

use App\Models\Zone;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TablesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // ── Número / Nombre de mesa ──────────────────────────────
                TextColumn::make('number')
                    ->label('Mesa')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->size('lg')
                    ->prefix('#'),

                // ── Zona / Sala ──────────────────────────────────────────
                TextColumn::make('zone.name')
                    ->label('Zona')
                    ->searchable()
                    ->badge()
                    ->color('gray')
                    ->placeholder('Sin zona'),

                // ── Estado con colores semafóricos ───────────────────────
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'occupied'  => 'danger',
                        'reserved'  => 'warning',
                        'cleaning'  => 'info',
                        'inactive'  => 'gray',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'available' => '✅ Disponible',
                        'occupied'  => '🔴 Ocupada',
                        'reserved'  => '🟡 Reservada',
                        'cleaning'  => '🧹 En limpieza',
                        'inactive'  => '⛔ Inactiva',
                        default     => $state,
                    })
                    ->sortable(),

                // ── Forma de la mesa ─────────────────────────────────────
                TextColumn::make('shape')
                    ->label('Forma')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'rectangle' => '▭ Rectangular',
                        'circle'    => '⭕ Circular',
                        'square'    => '□ Cuadrada',
                        default     => $state,
                    })
                    ->badge()
                    ->color('gray'),

                // ── Capacidad ────────────────────────────────────────────
                TextColumn::make('capacity')
                    ->label('Capacidad')
                    ->numeric()
                    ->sortable()
                    ->suffix(' personas')
                    ->icon('heroicon-o-user-group'),

                // ── Activa ───────────────────────────────────────────────
                IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                // ── Orden activo ─────────────────────────────────────────
                TextColumn::make('activeOrder.number')
                    ->label('Pedido Activo')
                    ->placeholder('—')
                    ->badge()
                    ->color('warning')
                    ->prefix('#'),

                // ── Columnas técnicas (ocultas por defecto) ───────────────
                TextColumn::make('pos_x')
                    ->label('Posición X')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('pos_y')
                    ->label('Posición Y')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('width')
                    ->label('Ancho')
                    ->numeric()
                    ->suffix(' px')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('height')
                    ->label('Alto')
                    ->numeric()
                    ->suffix(' px')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            // ── Filtros ──────────────────────────────────────────────────
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'available' => '✅ Disponible',
                        'occupied'  => '🔴 Ocupada',
                        'reserved'  => '🟡 Reservada',
                        'cleaning'  => '🧹 En limpieza',
                        'inactive'  => '⛔ Inactiva',
                    ]),

                SelectFilter::make('zone_id')
                    ->label('Zona')
                    ->relationship('zone', 'name'),

                SelectFilter::make('shape')
                    ->label('Forma')
                    ->options([
                        'rectangle' => '▭ Rectangular',
                        'circle'    => '⭕ Circular',
                        'square'    => '□ Cuadrada',
                    ]),

                SelectFilter::make('is_active')
                    ->label('Activa')
                    ->options([
                        '1' => 'Sí',
                        '0' => 'No',
                    ]),
            ])

            // ── Ordenado por defecto ─────────────────────────────────────
            ->defaultSort('number', 'asc')

            // ── Acciones por fila ────────────────────────────────────────
            ->recordActions([
                \Filament\Actions\Action::make('open_menu')
                    ->label('Ver Menú QR')
                    ->icon('heroicon-o-qr-code')
                    ->color('info')
                    ->url(fn (\App\Models\Table $record) => $record->getMenuUrl())
                    ->openUrlInNewTab(),

                EditAction::make()
                    ->label('Editar'),
            ])

            // ── Acciones de toolbar ──────────────────────────────────────
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),

                    \Filament\Actions\BulkAction::make('mark_available')
                        ->label('Marcar como Disponible')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['status' => 'available'])),

                    \Filament\Actions\BulkAction::make('mark_inactive')
                        ->label('Marcar como Inactiva')
                        ->icon('heroicon-o-x-circle')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_active' => false])),
                ]),
            ]);
    }
}
