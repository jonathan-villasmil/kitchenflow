<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Models\StockMovement;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class DeductInventoryOnOrderPayment
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderPaid $event): void
    {
        $order = $event->order;

        // Ensure we only process if the order actually has items
        if (!$order || !$order->items) return;

        DB::transaction(function () use ($order) {
            foreach ($order->items as $orderItem) {
                $dish = $orderItem->dish;
                
                if (!$dish || !$dish->ingredients) continue;

                foreach ($dish->ingredients as $ingredientLine) {
                    $inventoryItem = $ingredientLine; // due to belongsToMany relationship, this returns InventoryItem
                    $requiredQuantity = $ingredientLine->pivot->quantity;
                    $totalDeduction = $requiredQuantity * $orderItem->quantity;

                    // Deduct stock
                    $inventoryItem->stock_current -= $totalDeduction;
                    $inventoryItem->save();

                    // Log movement
                    StockMovement::create([
                        'inventory_item_id' => $inventoryItem->id,
                        'user_id' => auth()->id() ?? 1, // System fallback
                        'stock_old' => $inventoryItem->stock_current + $totalDeduction,
                        'stock_new' => $inventoryItem->stock_current,
                        'type' => 'out',
                        'quantity' => $totalDeduction,
                        'reason' => "Venta automática (Pedido #{$order->id})",
                    ]);
                }
            }
        });
    }
}
