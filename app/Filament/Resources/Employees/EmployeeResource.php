<?php

namespace App\Filament\Resources\Employees;

use App\Filament\Resources\Employees\Pages\CreateEmployee;
use App\Filament\Resources\Employees\Pages\EditEmployee;
use App\Filament\Resources\Employees\Pages\ListEmployees;
use App\Models\Employee;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;
    protected static ?string $recordTitleAttribute = 'first_name';

    public static function getNavigationGroup(): ?string { return 'Personas'; }
    public static function getModelLabel(): string { return 'Empleado'; }
    public static function getPluralModelLabel(): string { return 'Empleados'; }

    public static function form(Schema $form): Schema
    {
        return $form
            ->components([
                \Filament\Schemas\Components\Section::make('Datos del Empleado')
                    ->schema([
                        Forms\Components\Hidden::make('restaurant_id')
                            ->default(fn () => auth()->user()->restaurant_id ?? 1),

                        Forms\Components\TextInput::make('first_name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),
                            
                        Forms\Components\TextInput::make('last_name')
                            ->label('Apellidos')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('dni')
                            ->label('DNI / Documento')
                            ->maxLength(255),

                        Forms\Components\Select::make('position')
                            ->label('Puesto / Cargo')
                            ->options([
                                'Manager' => 'Manager / Gerente',
                                'Cajero' => 'Cajero / Recepción',
                                'Camarero' => 'Camarero / Sala',
                                'Cocinero' => 'Cocinero / Chef',
                                'Limpieza' => 'Personal de Limpieza',
                            ])
                            ->searchable(),

                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->label('Teléfono')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->label('Email')
                            ->maxLength(255),
                            
                        Forms\Components\DatePicker::make('hire_date')
                            ->label('Fecha de Contratación'),

                        Forms\Components\TextInput::make('hourly_rate')
                            ->label('Precio por Hora (€)')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('€'),

                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->label('Usuario del Sistema (Acceso)')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Asocia este empleado a una cuenta de usuario si necesita acceder al panel o POS.'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre Completo')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['first_name'])
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('position')
                    ->label('Cargo')
                    ->searchable()
                    ->badge(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable(),

                Tables\Columns\TextColumn::make('hire_date')
                    ->label('Contratado el')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('hourly_rate')
                    ->label('Tarifa/h')
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('position')
                    ->label('Cargo')
                    ->options([
                        'Manager' => 'Manager',
                        'Cajero' => 'Cajero',
                        'Camarero' => 'Camarero',
                        'Cocinero' => 'Cocinero',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Estado Activo'),
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
            'index' => ListEmployees::route('/'),
            'create' => CreateEmployee::route('/create'),
            'edit' => EditEmployee::route('/{record}/edit'),
        ];
    }
}
