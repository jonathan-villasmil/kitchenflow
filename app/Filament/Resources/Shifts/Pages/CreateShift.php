<?php

namespace App\Filament\Resources\Shifts\Pages;

use App\Filament\Resources\Concerns\RestaurantFormScoping;
use App\Filament\Resources\Shifts\ShiftResource;
use App\Filament\Resources\Shifts\Pages\Concerns\SetsShiftRestaurantFromEmployee;
use Filament\Resources\Pages\CreateRecord;

class CreateShift extends CreateRecord
{
    use SetsShiftRestaurantFromEmployee;

    protected static string $resource = ShiftResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->setShiftRestaurantFromEmployee(
            RestaurantFormScoping::forceRestaurantOnFormData($data)
        );
    }
}
