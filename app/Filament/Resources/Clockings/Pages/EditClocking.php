<?php

namespace App\Filament\Resources\Clockings\Pages;

use App\Filament\Resources\Clockings\ClockingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditClocking extends EditRecord
{
    protected static string $resource = ClockingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
