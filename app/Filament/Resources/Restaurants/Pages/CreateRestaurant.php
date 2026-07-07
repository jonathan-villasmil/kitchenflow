<?php

namespace App\Filament\Resources\Restaurants\Pages;

use App\Filament\Resources\RestaurantResource;
use App\Models\Restaurant;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateRestaurant extends CreateRecord
{
    protected static string $resource = RestaurantResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $baseSlug = Str::slug($data['name']);
        $slug = $baseSlug;
        $suffix = 2;

        while (Restaurant::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        $data['slug'] = $slug;

        return $data;
    }
}
