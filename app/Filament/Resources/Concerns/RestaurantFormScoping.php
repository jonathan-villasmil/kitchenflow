<?php

namespace App\Filament\Resources\Concerns;

use App\Models\Restaurant;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class RestaurantFormScoping
{
    public static function canChooseRestaurant(): bool
    {
        return (bool) auth()->user()?->hasRole('super_admin');
    }

    public static function currentRestaurantId(): ?int
    {
        return auth()->user()?->restaurant_id;
    }

    public static function selectedRestaurantId(mixed $state = null): ?int
    {
        if (! self::canChooseRestaurant()) {
            return self::currentRestaurantId();
        }

        return filled($state) ? (int) $state : self::currentRestaurantId();
    }

    public static function restaurantOptions(): Collection
    {
        $query = Restaurant::query()->orderBy('name');

        if (! self::canChooseRestaurant()) {
            $restaurantId = self::currentRestaurantId();
            $query->whereKey($restaurantId ?: 0);
        }

        return $query->pluck('name', 'id');
    }

    public static function restaurantSelect(string $name = 'restaurant_id'): Select
    {
        return Select::make($name)
            ->label('Restaurante')
            ->options(fn () => self::restaurantOptions())
            ->default(fn () => self::currentRestaurantId())
            ->disabled(fn () => ! self::canChooseRestaurant())
            ->dehydrated(true)
            ->required()
            ->searchable()
            ->preload()
            ->live();
    }

    public static function scopeToRestaurant(Builder $query, mixed $restaurantId = null, string $column = 'restaurant_id'): Builder
    {
        if (self::canChooseRestaurant() && blank($restaurantId)) {
            return $query;
        }

        $restaurantId = self::selectedRestaurantId($restaurantId);

        return $restaurantId
            ? $query->where($column, $restaurantId)
            : $query->whereRaw('1 = 0');
    }

    public static function forceRestaurantOnFormData(array $data): array
    {
        if (! self::canChooseRestaurant()) {
            $data['restaurant_id'] = self::currentRestaurantId();
        }

        return $data;
    }
}
