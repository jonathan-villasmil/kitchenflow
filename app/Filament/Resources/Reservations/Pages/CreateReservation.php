<?php

namespace App\Filament\Resources\Reservations\Pages;

use App\Filament\Resources\Concerns\ForcesRestaurantFormData;
use App\Filament\Resources\Reservations\ReservationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateReservation extends CreateRecord
{
    use ForcesRestaurantFormData;

    protected static string $resource = ReservationResource::class;
}
