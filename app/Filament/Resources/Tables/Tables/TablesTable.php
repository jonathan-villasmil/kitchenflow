<?php

namespace App\Filament\Resources\Tables\Tables;

use App\Filament\Resources\Concerns\RestaurantFormScoping;
use App\Models\Zone;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                    ->relationship('zone', 'name',
                        modifyQueryUsing: fn (Builder $query) => RestaurantFormScoping::scopeToRestaurant($query)
                    ),

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
                \Filament\Actions\Action::make('show_qr')
                    ->label('QR Carta')
                    ->icon('heroicon-o-qr-code')
                    ->color('info')
                    ->modalHeading(fn (\App\Models\Table $record) => "QR — Mesa {$record->number}")
                    ->modalDescription('Escanea este código QR con el móvil para acceder a la carta digital de esta mesa.')
                    ->modalWidth('sm')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->modalContent(function (\App\Models\Table $record) {
                        $url    = $record->getMenuUrl();
                        $qrUrl  = 'https://api.qrserver.com/v1/create-qr-code/?size=260x260&margin=10&data=' . urlencode($url);
                        $html   = '
                            <div style="text-align:center;padding:1rem;">
                                <img src="' . $qrUrl . '"
                                     alt="QR Mesa ' . $record->number . '"
                                     style="width:260px;height:260px;border-radius:12px;border:1px solid #e5e7eb;margin:0 auto 1rem;" />

                                <p style="font-size:11px;color:#6b7280;word-break:break-all;margin-bottom:1rem;background:#f9fafb;padding:8px;border-radius:8px;">
                                    ' . $url . '
                                </p>

                                <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap;">
                                    <a href="' . $url . '" target="_blank"
                                       style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:#f97316;color:#fff;border-radius:8px;font-weight:700;font-size:13px;text-decoration:none;">
                                        🔗 Abrir carta
                                    </a>
                                    <a href="' . $qrUrl . '&format=png" download="mesa-' . $record->number . '-qr.png"
                                       style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:#1f2937;color:#fff;border-radius:8px;font-weight:700;font-size:13px;text-decoration:none;">
                                        ⬇️ Descargar QR
                                    </a>
                                </div>

                                <p style="font-size:11px;color:#9ca3af;margin-top:1rem;">
                                    💡 Para imprimir: botón derecho sobre el QR → <strong>Guardar imagen</strong>, luego imprímelo al tamaño que necesites.
                                </p>
                            </div>
                        ';
                        return new \Illuminate\Support\HtmlString($html);
                    }),

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
