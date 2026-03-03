<?php

namespace App\Filament\Resources\Reservations\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ReservationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('restaurant_id')
                    ->required()
                    ->numeric(),
                TextInput::make('table_id')
                    ->numeric(),
                TextInput::make('customer_id')
                    ->numeric(),
                TextInput::make('user_id')
                    ->numeric(),
                TextInput::make('guest_name')
                    ->required(),
                TextInput::make('guest_phone')
                    ->tel(),
                TextInput::make('guest_email')
                    ->email(),
                TextInput::make('party_size')
                    ->required()
                    ->numeric(),
                DateTimePicker::make('reserved_at')
                    ->required(),
                TextInput::make('duration_minutes')
                    ->required()
                    ->numeric()
                    ->default(90),
                Select::make('status')
                    ->options([
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'seated' => 'Seated',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'no_show' => 'No show',
        ])
                    ->default('pending')
                    ->required(),
                Textarea::make('notes')
                    ->columnSpanFull(),
                TextInput::make('source')
                    ->required()
                    ->default('phone'),
            ]);
    }
}
