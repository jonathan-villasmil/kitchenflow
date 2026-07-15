<?php

namespace App\Filament\Resources\Tables\Pages;

use App\Filament\Resources\Concerns\ForcesRestaurantFormData;
use App\Filament\Resources\Tables\TableResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTable extends CreateRecord
{
    use ForcesRestaurantFormData;

    protected static string $resource = TableResource::class;
}
