<?php

namespace App\Filament\Resources\Clockings\Pages;

use App\Filament\Resources\Clockings\ClockingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListClockings extends ListRecords
{
    protected static string $resource = ClockingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
