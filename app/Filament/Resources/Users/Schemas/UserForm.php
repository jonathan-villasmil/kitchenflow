<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Detalles de la Cuenta')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre Completo')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Correo Electrónico')
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->required(),

                        TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => \Illuminate\Support\Facades\Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create'),

                        TextInput::make('pin')
                            ->label('PIN de Caja (4 dígitos)')
                            ->numeric()
                            ->password()
                            ->maxLength(4)
                            ->minLength(4)
                            ->revealable()
                            ->unique(ignoreRecord: true),

                        Select::make('roles')
                            ->label('Rol del Sistema (Permisos)')
                            ->relationship('roles', 'name')
                            ->preload()
                            ->searchable()
                            ->required(),

                        Select::make('restaurant_id')
                            ->label('Restaurante')
                            ->relationship('restaurant', 'name')
                            ->default(1)
                            ->required(),
                    ])->columns(2),
            ]);
    }
}
