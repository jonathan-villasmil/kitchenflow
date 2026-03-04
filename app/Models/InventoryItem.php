<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    protected $fillable = [
        'restaurant_id',
        'inventory_category_id',
        'name',
        'unit',
        'stock_current',
        'stock_minimum',
        'stock_maximum',
        'cost_per_unit',
        'track_stock',
        'is_active',
    ];

    protected $casts = [
        'stock_current' => 'decimal:3',
        'stock_minimum' => 'decimal:3',
        'stock_maximum' => 'decimal:3',
        'cost_per_unit' => 'decimal:4',
        'track_stock' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(InventoryCategory::class, 'inventory_category_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
