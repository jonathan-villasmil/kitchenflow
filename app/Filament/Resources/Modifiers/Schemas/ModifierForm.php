<?php

namespace App\Filament\Resources\Modifiers\Schemas;

use App\Filament\Resources\Concerns\RestaurantFormScoping;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ModifierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('modifier_group_id')
                    ->relationship('modifierGroup', 'name',
                        modifyQueryUsing: fn (Builder $query) => RestaurantFormScoping::scopeToRestaurant($query)
                    )
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->prefix('$'),
            ]);
    }
}
