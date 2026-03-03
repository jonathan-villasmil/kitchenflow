<?php

namespace App\Filament\Resources\Orders\Schemas;

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
                Select::make('restaurant_id')
                    ->relationship('restaurant', 'name')
                    ->required(),
                Select::make('table_id')
                    ->relationship('table', 'id'),
                Select::make('customer_id')
                    ->relationship('customer', 'name'),
                TextInput::make('user_id')
                    ->numeric(),
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
