<?php

namespace App\Filament\Resources\Reservations\Schemas;

use App\Models\Table;
use App\Models\Customer;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ReservationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    Section::make('Datos del cliente')
                        ->schema([
                            Select::make('customer_id')
                                ->label('Cliente registrado')
                                ->relationship('customer', 'name')
                                ->searchable()
                                ->placeholder('Seleccionar cliente o rellenar abajo')
                                ->nullable(),

                            Grid::make(2)->schema([
                                TextInput::make('guest_name')
                                    ->label('Nombre del cliente')
                                    ->required()
                                    ->placeholder('Juan García'),

                                TextInput::make('guest_phone')
                                    ->label('Teléfono')
                                    ->tel()
                                    ->placeholder('+34 600 000 000'),
                            ]),

                            TextInput::make('guest_email')
                                ->label('Email')
                                ->email()
                                ->placeholder('cliente@email.com'),
                        ]),

                    Section::make('Detalles de la reserva')
                        ->schema([
                            Select::make('restaurant_id')
                                ->label('Restaurante')
                                ->relationship('restaurant', 'name')
                                ->required()
                                ->searchable()
                                ->preload(),

                            Grid::make(2)->schema([
                                DateTimePicker::make('reserved_at')
                                    ->label('Fecha y hora')
                                    ->required()
                                    ->displayFormat('d/m/Y H:i')
                                    ->minutesStep(15),

                                TextInput::make('party_size')
                                    ->label('Número de personas')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(100),
                            ]),

                            Grid::make(2)->schema([
                                Select::make('table_id')
                                    ->label('Mesa asignada')
                                    ->options(
                                        Table::where('status', 'available')
                                            ->get()
                                            ->mapWithKeys(fn ($t) => [$t->id => "Mesa {$t->number} ({$t->capacity} pax)"])
                                    )
                                    ->searchable()
                                    ->nullable()
                                    ->placeholder('Sin asignar'),

                                TextInput::make('duration_minutes')
                                    ->label('Duración estimada')
                                    ->numeric()
                                    ->default(90)
                                    ->suffix('min'),
                            ]),

                            Select::make('status')
                                ->label('Estado')
                                ->options([
                                    'pending'   => '⏳ Pendiente de confirmar',
                                    'confirmed' => '✅ Confirmada',
                                    'seated'    => '🍽️ En mesa',
                                    'completed' => '✔️ Completada',
                                    'cancelled' => '❌ Cancelada',
                                    'no_show'   => '👻 No se presentó',
                                ])
                                ->default('pending')
                                ->required(),

                            Select::make('source')
                                ->label('Origen de la reserva')
                                ->options([
                                    'phone'   => '📞 Teléfono',
                                    'web'     => '🌐 Web',
                                    'walkin'  => '🚶 En persona',
                                    'widget'  => '📱 Widget / App',
                                ])
                                ->default('phone')
                                ->required(),

                            Textarea::make('notes')
                                ->label('Notas internas')
                                ->rows(2)
                                ->placeholder('Ej: Celiaco, cumpleaños, zona tranquila...')
                                ->columnSpanFull(),
                        ]),
                ]),
            ]);
    }
}
