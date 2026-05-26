<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = [
        'restaurant_id',
        'inventory_item_id',
        'user_id',
        'supplier_id',
        'type',
        'quantity',
        'unit_cost',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_cost' => 'decimal:4',
    ];

    protected static function booted(): void
    {
        static::created(function (StockMovement $movement) {
            $item = $movement->item;
            if (!$item || !$item->track_stock) {
                return;
            }

            if ($movement->type === 'purchase') {
                $item->stock_current += $movement->quantity;
                // Update average cost (simplistic approach):
                if ($movement->unit_cost && $movement->unit_cost > 0) {
                    $item->cost_per_unit = $movement->unit_cost;
                }
            } elseif ($movement->type === 'sale' || $movement->type === 'waste' || $movement->type === 'transfer') {
                $item->stock_current -= $movement->quantity;
            } elseif ($movement->type === 'adjustment') {
                $item->stock_current = $movement->quantity; 
            }

            $item->save();

            // Broadcast stock updates in real-time to the Digital Menu
            $dishes = Dish::whereHas('ingredients', function ($query) use ($item) {
                $query->where('inventory_items.id', $item->id);
            })->with('ingredients')->get();

            foreach ($dishes as $dish) {
                $stock = $dish->calculateStock();
                event(new \App\Events\DishStockUpdated($dish, $stock['status'], $stock['portions']));
            }
        });
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
