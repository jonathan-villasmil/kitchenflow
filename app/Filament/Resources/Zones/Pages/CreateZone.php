<?php

namespace App\Filament\Resources\Zones\Pages;

use App\Filament\Resources\Concerns\ForcesRestaurantFormData;
use App\Filament\Resources\ZoneResource;
use Filament\Resources\Pages\CreateRecord;

class CreateZone extends CreateRecord
{
    use ForcesRestaurantFormData;

    protected static string $resource = ZoneResource::class;
}
