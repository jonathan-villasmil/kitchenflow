<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Filament\Resources\Concerns\RestaurantFormScoping;
use App\Models\Customer;
use App\Models\Table;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('number')
                    ->required(),
                RestaurantFormScoping::restaurantSelect(),
                Select::make('table_id')
                    ->options(fn ($get) =>
                        Table::where('restaurant_id', RestaurantFormScoping::selectedRestaurantId($get('restaurant_id')))
                            ->pluck('number', 'id')
                    ),
                Select::make('customer_id')
                    ->label('Cliente Asociado')
                    ->options(fn ($get) =>
                        Customer::where('restaurant_id', RestaurantFormScoping::selectedRestaurantId($get('restaurant_id')))
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Select::make('user_id')
                    ->label('Usuario')
                    ->options(fn ($get) =>
                        User::where('restaurant_id', RestaurantFormScoping::selectedRestaurantId($get('restaurant_id')))
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->nullable(),
                Select::make('type')
                    ->options(['dine_in' => 'Dine in', 'takeaway' => 'Takeaway', 'delivery' => 'Delivery'])
                    ->default('dine_in')
                    ->required(),
                Select::make('status')
                    ->options([
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'preparing' => 'Preparing',
            'ready' => 'Ready',
            'delivered' => 'Delivered',
            'paid' => 'Paid',
            'cancelled' => 'Cancelled',
        ])
                    ->default('pending')
                    ->required(),
                TextInput::make('guests')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('subtotal')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('tax_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('discount_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('total')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Textarea::make('notes')
                    ->columnSpanFull(),
                DateTimePicker::make('opened_at'),
                DateTimePicker::make('closed_at'),
            ]);
    }
}
