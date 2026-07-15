<?php

namespace App\Filament\Resources\Concerns;

trait ForcesRestaurantFormData
{
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return RestaurantFormScoping::forceRestaurantOnFormData($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return RestaurantFormScoping::forceRestaurantOnFormData($data);
    }
}
