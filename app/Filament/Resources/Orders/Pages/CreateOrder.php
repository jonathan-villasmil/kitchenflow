<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Concerns\ForcesRestaurantFormData;
use App\Filament\Resources\Orders\OrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    use ForcesRestaurantFormData;

    protected static string $resource = OrderResource::class;
}
