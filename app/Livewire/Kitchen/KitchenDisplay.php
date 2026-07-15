<?php

namespace App\Livewire\Kitchen;

use App\Models\Order;
use App\Models\OrderItem;
use Livewire\Attributes\On;
use Livewire\Component;

class KitchenDisplay extends Component
{
    // ── KDS State ──────────────────────────────────────────────────────
    public string $station = 'hot'; // hot, cold, bar, bakery
    public bool $soundEnabled = true;

    // We can listen to Reverb broadcasting here 
    // #[On('echo:orders,OrderSentToKitchen')]
    // public function refreshOrders() { $this->dispatch('play-sound'); }

    public function mount()
    {
        $user = auth()->user();
        if (!$user || !$user->hasAnyRole(['super_admin', 'manager', 'cocinero'])) {
            abort(403, 'No tienes permiso para acceder a la pantalla de cocina.');
        }

        $this->station = request()->query('station', 'hot');
    }

    private function restaurantId(): ?int
    {
        return auth()->user()?->restaurant_id;
    }

    private function findOrderItemForCurrentRestaurant(int $itemId): ?OrderItem
    {
        return OrderItem::whereKey($itemId)
            ->whereHas('order', fn ($query) =>
                $query->where('restaurant_id', $this->restaurantId())
            )
            ->first();
    }

    public function markAsReady($itemId)
    {
        $item = $this->findOrderItemForCurrentRestaurant((int) $itemId);
        if ($item) {
            $item->update([
                'status'   => 'ready',
                'ready_at' => now(),
            ]);

            // Check if entire order is ready
            $order = $item->order;
            if ($order->items()->whereNotIn('status', ['ready', 'cancelled'])->count() === 0) {
                $order->update(['status' => 'ready']);
            }

            broadcast(new \App\Events\OrderReadyForPickup($order))->toOthers();
        }
    }

    public function getActiveOrdersProperty()
    {
        $user = auth()->user();
        $restaurantId = $user->restaurant_id ?? 1;

        // Get orders that have items for this station that are NOT ready/delivered
        // Eager load relationships to prevent N+1 queries in KDS rendering
        return Order::with([
                'table',
                'user',
                'items' => function ($query) {
                    $query->whereIn('status', ['sent', 'preparing'])
                          ->whereHas('dish', fn ($q) => $q->where('kitchen_station', $this->station))
                          ->with(['modifiers', 'dish']);
                }
            ])
            ->where('restaurant_id', $restaurantId)
            ->whereHas('items', function ($query) {
                $query->whereIn('status', ['sent', 'preparing'])
                      ->whereHas('dish', fn ($q) => $q->where('kitchen_station', $this->station));
            })
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function render()
    {
        return view('livewire.kitchen.kitchen-display')
            ->layout('layouts.pos');
    }
}
