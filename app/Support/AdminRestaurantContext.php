<?php

namespace App\Support;

use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AdminRestaurantContext
{
    public const SESSION_KEY = 'admin_restaurant_context_id';

    public static function canChooseRestaurant(): bool
    {
        return (bool) auth()->user()?->hasRole('super_admin');
    }

    public static function selectedId(): ?int
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        if (! $user->hasRole('super_admin')) {
            return $user->restaurant_id;
        }

        $restaurantId = session(self::SESSION_KEY);

        return filled($restaurantId) ? (int) $restaurantId : null;
    }

    public static function selectedLabel(): string
    {
        $restaurantId = self::selectedId();

        if (! $restaurantId) {
            return 'Todos los restaurantes';
        }

        return Restaurant::whereKey($restaurantId)->value('name') ?? 'Restaurante seleccionado';
    }

    public static function restaurantOptions(): Collection
    {
        return Restaurant::query()
            ->orderBy('name')
            ->pluck('name', 'id');
    }

    public static function setForSuperAdmin(?int $restaurantId): void
    {
        if (! self::canChooseRestaurant()) {
            return;
        }

        if (! $restaurantId) {
            session()->forget(self::SESSION_KEY);

            return;
        }

        if (Restaurant::whereKey($restaurantId)->exists()) {
            session([self::SESSION_KEY => $restaurantId]);
        }
    }

    public static function scope(Builder $query, string $column = 'restaurant_id'): Builder
    {
        $restaurantId = self::selectedId();

        return $restaurantId
            ? $query->where($column, $restaurantId)
            : $query;
    }

    public static function scopeThroughOrder(Builder $query): Builder
    {
        $restaurantId = self::selectedId();

        return $restaurantId
            ? $query->whereHas('order', fn (Builder $orderQuery) => $orderQuery->where('restaurant_id', $restaurantId))
            : $query;
    }
}
