<?php

namespace App\Filament\Resources\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

trait ScopedToRestaurant
{
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (!$user || $user->hasRole('super_admin')) {
            return $query;
        }

        $restaurantId = $user->restaurant_id;
        if (!$restaurantId) {
            return $query->whereRaw('1 = 0');
        }

        $model = $query->getModel();
        $table = $model->getTable();
        $column = property_exists(static::class, 'restaurantScopedColumn')
            ? static::$restaurantScopedColumn
            : 'restaurant_id';

        if ($column && Schema::hasColumn($table, $column)) {
            return $query->where($model->qualifyColumn($column), $restaurantId);
        }

        $relation = property_exists(static::class, 'restaurantScopedRelation')
            ? static::$restaurantScopedRelation
            : null;

        if ($relation) {
            return $query->whereHas($relation, fn (Builder $relationQuery) =>
                $relationQuery->where('restaurant_id', $restaurantId)
            );
        }

        return $query;
    }
}
