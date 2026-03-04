<?php

namespace App\Filament\Resources\Clockings;

use App\Filament\Resources\Clockings\Pages\CreateClocking;
use App\Filament\Resources\Clockings\Pages\EditClocking;
use App\Filament\Resources\Clockings\Pages\ListClockings;
use App\Models\Clocking;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ClockingResource extends Resource
{
    protected static ?string $model = Clocking::class;
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
                            ->relationship('employee', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                            ->label('Empleado')
                            ->searchable()
                            ->preload()
                            ->required()
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
                    ->relationship('employee', 'first_name')
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
