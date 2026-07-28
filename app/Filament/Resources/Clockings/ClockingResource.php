<?php

namespace App\Filament\Resources\Clockings;

use App\Filament\Resources\Concerns\ScopedToRestaurant;
use App\Filament\Resources\Concerns\RestaurantFormScoping;
use App\Filament\Resources\Clockings\Pages\CreateClocking;
use App\Filament\Resources\Clockings\Pages\EditClocking;
use App\Filament\Resources\Clockings\Pages\ListClockings;
use App\Models\Clocking;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ClockingResource extends Resource
{
    use ScopedToRestaurant;

    protected static ?string $model = Clocking::class;
    protected static ?string $restaurantScopedRelation = 'employee';
    protected static ?string $recordTitleAttribute = 'id';

    public static function getNavigationGroup(): ?string { return 'Personas'; }
    public static function getModelLabel(): string { return 'Fichaje Registrado'; }
    public static function getPluralModelLabel(): string { return 'Registro de Fichajes'; }

    public static function form(Schema $form): Schema
    {
        return $form
            ->components([
                \Filament\Schemas\Components\Section::make('Registro de Horas Manual')
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->relationship('employee', 'first_name',
                                modifyQueryUsing: fn (Builder $query) => RestaurantFormScoping::scopeToRestaurant($query)
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                            ->label('Empleado')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Select::make('shift_id')
                            ->relationship('shift', 'id',
                                modifyQueryUsing: fn (Builder $query) => $query
                                    ->whereHas('employee', fn (Builder $employeeQuery) => RestaurantFormScoping::scopeToRestaurant($employeeQuery))
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->employee->name . ' - ' . $record->date->format('d/m/Y') . ' ' . substr($record->start_time, 0, 5) . '-' . substr($record->end_time, 0, 5))
                            ->label('Turno planificado')
                            ->searchable()
                            ->preload()
                            ->helperText('Si se deja vacio, el sistema intentara enlazar el fichaje con el turno planificado mas cercano.')
                            ->columnSpanFull(),

                        Forms\Components\DateTimePicker::make('clocked_in_at')
                            ->label('Hora de Entrada')
                            ->required()
                            ->seconds(false),

                        Forms\Components\DateTimePicker::make('clocked_out_at')
                            ->label('Hora de Salida')
                            ->seconds(false),

                        Forms\Components\TextInput::make('total_minutes')
                            ->label('Minutos Totales Trabajados')
                            ->numeric()
                            ->disabled() // Computed typically by an observer or action
                            ->dehydrated(false)
                            ->helperText('Se calcula automáticamente si falta.'),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notas / Motivo de edición manual')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Empleado')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('clocked_in_at')
                    ->label('Entrada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('clocked_out_at')
                    ->label('Salida')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('En curso...'),

                Tables\Columns\TextColumn::make('shift.date')
                    ->label('Turno')
                    ->date('d/m/Y')
                    ->placeholder('Sin turno')
                    ->sortable(),

                Tables\Columns\TextColumn::make('attendance_status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'matched', 'open' => 'success',
                        'open_late', 'late' => 'warning',
                        'left_early', 'late_and_left_early' => 'danger',
                        'unplanned' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'matched' => 'Correcto',
                        'open' => 'En curso',
                        'open_late' => 'En curso tarde',
                        'late' => 'Entrada tarde',
                        'left_early' => 'Salida anticipada',
                        'late_and_left_early' => 'Tarde y salida anticipada',
                        'unplanned' => 'Sin turno',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('total_minutes')
                    ->label('Horas Trabajadas')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        if (!$state) return '0h 0m';
                        $hours = floor($state / 60);
                        $mins = $state % 60;
                        return "{$hours}h {$mins}m";
                    }),
            ])
            ->defaultSort('clocked_in_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('employee_id')
                    ->relationship('employee', 'first_name',
                        modifyQueryUsing: fn (Builder $query) => RestaurantFormScoping::scopeToRestaurant($query)
                    )
                    ->label('Empleado')
                    ->searchable(),
                Tables\Filters\Filter::make('active_clockings')
                    ->label('Fichajes en curso')
                    ->query(fn ($query) => $query->whereNull('clocked_out_at')),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
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
            'index' => ListClockings::route('/'),
            'create' => CreateClocking::route('/create'),
            'edit' => EditClocking::route('/{record}/edit'),
        ];
    }
}
