<?php

namespace App\Filament\Resources\Shifts;

use App\Filament\Resources\Shifts\Pages\CreateShift;
use App\Filament\Resources\Shifts\Pages\EditShift;
use App\Filament\Resources\Shifts\Pages\ListShifts;
use App\Models\Shift;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShiftResource extends Resource
{
    protected static ?string $model = Shift::class;
    protected static ?string $recordTitleAttribute = 'id';

    public static function getNavigationGroup(): ?string { return 'Personas'; }
    public static function getModelLabel(): string { return 'Turno'; }
    public static function getPluralModelLabel(): string { return 'Turnos'; }

    public static function form(Schema $form): Schema
    {
        return $form
            ->components([
                \Filament\Schemas\Components\Section::make('Programación de Turno')
                    ->schema([
                        Forms\Components\Hidden::make('restaurant_id')
                            ->default(fn () => auth()->user()->restaurant_id ?? 1),

                        Forms\Components\Select::make('employee_id')
                            ->relationship('employee', 'first_name') // Needs custom getter ideally, but valid for DB
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                            ->label('Empleado')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\DatePicker::make('date')
                            ->label('Fecha del Turno')
                            ->required()
                            ->default(now()),

                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'scheduled' => 'Programado',
                                'confirmed' => 'Confirmado',
                                'completed' => 'Completado',
                                'absent' => 'Ausente',
                            ])
                            ->required()
                            ->default('scheduled'),

                        Forms\Components\TimePicker::make('start_time')
                            ->label('Hora de Inicio')
                            ->seconds(false)
                            ->required(),

                        Forms\Components\TimePicker::make('end_time')
                            ->label('Hora de Fin')
                            ->seconds(false)
                            ->required(),

                        Forms\Components\TextInput::make('break_minutes')
                            ->label('Minutos de Descanso')
                            ->numeric()
                            ->default(0)
                            ->step(15),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notas Adicionales')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Fecha')
                    ->date('D d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Empleado')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('Inicio')
                    ->time('H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_time')
                    ->label('Fin')
                    ->time('H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'scheduled' => 'gray',
                        'confirmed' => 'info',
                        'completed' => 'success',
                        'absent' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'scheduled' => 'Programado',
                        'confirmed' => 'Confirmado',
                        'completed' => 'Completado',
                        'absent' => 'Ausente',
                    }),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('employee_id')
                    ->relationship('employee', 'first_name')
                    ->label('Empleado')
                    ->searchable(),
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
            'index' => ListShifts::route('/'),
            'create' => CreateShift::route('/create'),
            'edit' => EditShift::route('/{record}/edit'),
        ];
    }
}
