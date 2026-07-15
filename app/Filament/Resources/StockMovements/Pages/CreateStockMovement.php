<?php

namespace App\Filament\Resources\StockMovements\Pages;

use App\Filament\Resources\Concerns\ForcesRestaurantFormData;
use App\Filament\Resources\StockMovements\StockMovementResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStockMovement extends CreateRecord
{
    use ForcesRestaurantFormData;

    protected static string $resource = StockMovementResource::class;
}
