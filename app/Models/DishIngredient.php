<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DishIngredient extends Model
{
    protected $fillable = [
        'dish_id',
        'inventory_item_id',
        'quantity',
    ];

    public function dish()
    {
        return $this->belongsTo(Dish::class);
    }

    public function inventoryItem()
    {
        return $this->belongsTo(\App\Models\InventoryItem::class);
    }
}
