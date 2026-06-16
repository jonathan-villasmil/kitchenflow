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
            $deductions = [];

            // 1. Gather and aggregate all required quantities by inventory item ID
            foreach ($order->items as $orderItem) {
                $dish = $orderItem->dish;
                if (!$dish || !$dish->ingredients) continue;

                foreach ($dish->ingredients as $ingredientLine) {
                    $itemId = $ingredientLine->id;
                    $requiredQuantity = $ingredientLine->pivot->quantity;
                    $totalDeduction = $requiredQuantity * $orderItem->quantity;

                    if (!isset($deductions[$itemId])) {
                        $deductions[$itemId] = 0.0;
                    }
                    $deductions[$itemId] += $totalDeduction;
                }
            }

            // 2. Sort the inventory item IDs ascending to prevent deadlocks
            ksort($deductions);

            // 3. Process the deductions in sorted order
            foreach ($deductions as $itemId => $totalDeduction) {
                // Lock the inventory item row in the DB to prevent race conditions during concurrent payments
                $inventoryItem = \App\Models\InventoryItem::where('id', $itemId)
                    ->lockForUpdate()
                    ->first();

                if (!$inventoryItem) continue;

                // Log movement and trigger stock deduction via Model Hook
                StockMovement::create([
                    'restaurant_id' => $order->restaurant_id,
                    'inventory_item_id' => $inventoryItem->id,
                    'user_id' => $order->user_id ?? auth()->id() ?? 1,
                    'type' => 'sale',
                    'quantity' => $totalDeduction,
                    'notes' => "Venta automática (Pedido #{$order->id})",
                ]);
            }
        });
    }
}
