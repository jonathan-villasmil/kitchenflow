<?php

namespace App\Filament\Resources\Clockings\Pages;

use App\Filament\Resources\Clockings\ClockingResource;
use App\Support\ShiftClockingMatcher;
use Filament\Resources\Pages\CreateRecord;

class CreateClocking extends CreateRecord
{
    protected static string $resource = ClockingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return ShiftClockingMatcher::applyToClockingData($data);
    }
}
