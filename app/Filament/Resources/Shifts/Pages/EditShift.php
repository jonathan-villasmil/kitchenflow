<?php

namespace App\Filament\Resources\Shifts\Pages;

use App\Filament\Resources\Concerns\RestaurantFormScoping;
use App\Filament\Resources\Shifts\Pages\Concerns\SetsShiftRestaurantFromEmployee;
use App\Filament\Resources\Shifts\ShiftResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditShift extends EditRecord
{
    use SetsShiftRestaurantFromEmployee;

    protected static string $resource = ShiftResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->setShiftRestaurantFromEmployee(
            RestaurantFormScoping::forceRestaurantOnFormData($data)
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
