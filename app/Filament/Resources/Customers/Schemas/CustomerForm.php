<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Datos del Cliente')
                    ->schema([
                        \Filament\Forms\Components\Hidden::make('restaurant_id')
                            ->default(fn () => auth()->user()->restaurant_id ?? 1),

                        TextInput::make('name')
                            ->label('Nombre Completo')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Correo Electrónico')
                            ->email()
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label('Teléfono')
                            ->tel()
                            ->maxLength(255),

                        DatePicker::make('birthday')
                            ->label('Fecha de Nacimiento')
                            ->maxDate(now()),

                        TextInput::make('loyalty_points')
                            ->label('Puntos Acumulados')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefixIcon('heroicon-m-star'),

                        Textarea::make('notes')
                            ->label('Notas / Preferencias')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}
