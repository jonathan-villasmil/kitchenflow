<?php

namespace App\Filament\Resources\ModifierGroups\Pages;

use App\Filament\Resources\Concerns\ForcesRestaurantFormData;
use App\Filament\Resources\ModifierGroups\ModifierGroupResource;
use Filament\Resources\Pages\CreateRecord;

class CreateModifierGroup extends CreateRecord
{
    use ForcesRestaurantFormData;

    protected static string $resource = ModifierGroupResource::class;
}
