<?php

namespace App\Filament\Resources\Dishes\Pages;

use App\Filament\Resources\Concerns\ForcesRestaurantFormData;
use App\Filament\Resources\Dishes\DishResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDish extends CreateRecord
{
    use ForcesRestaurantFormData;

    protected static string $resource = DishResource::class;
}
