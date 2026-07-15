<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Concerns\ForcesRestaurantFormData;
use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    use ForcesRestaurantFormData;

    protected static string $resource = UserResource::class;
}
