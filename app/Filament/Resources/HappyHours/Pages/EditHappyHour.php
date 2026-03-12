<?php

namespace App\Filament\Resources\HappyHours\Pages;

use App\Filament\Resources\HappyHours\HappyHourResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHappyHour extends EditRecord
{
    protected static string $resource = HappyHourResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
