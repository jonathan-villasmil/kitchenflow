<?php

namespace App\Filament\Resources\InventoryItems\Pages;

use App\Filament\Resources\Concerns\ForcesRestaurantFormData;
use App\Filament\Resources\InventoryItems\InventoryItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInventoryItem extends CreateRecord
{
    use ForcesRestaurantFormData;

    protected static string $resource = InventoryItemResource::class;
}
