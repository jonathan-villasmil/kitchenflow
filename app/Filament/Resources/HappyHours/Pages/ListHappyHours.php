<?php

namespace App\Filament\Resources\HappyHours\Pages;

use App\Filament\Resources\HappyHours\HappyHourResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHappyHours extends ListRecords
{
    protected static string $resource = HappyHourResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
