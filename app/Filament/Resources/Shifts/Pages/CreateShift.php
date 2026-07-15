<?php

namespace App\Filament\Resources\Shifts\Pages;

use App\Filament\Resources\Concerns\ForcesRestaurantFormData;
use App\Filament\Resources\Shifts\ShiftResource;
use Filament\Resources\Pages\CreateRecord;

class CreateShift extends CreateRecord
{
    use ForcesRestaurantFormData;

    protected static string $resource = ShiftResource::class;
}
