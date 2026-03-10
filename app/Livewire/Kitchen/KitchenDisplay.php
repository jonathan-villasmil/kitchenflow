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
        $this->station = request()->query('station', 'hot');
    }

    public function markAsReady($itemId)
    {
        $item = OrderItem::find($itemId);
        if ($item) {
            $item->update([
                'status'   => 'ready',
                'ready_at' => now(),
            ]);

            // Check if entire order is ready
            $order = $item->order;
            if ($order->items()->where('status', '!=', 'ready')->count() === 0) {
                $order->update(['status' => 'ready']);
            }
        }
    }

    public function getActiveOrdersProperty()
    {
        $user = auth()->user();
        $restaurantId = $user->restaurant_id ?? 1;

        // Get orders that have items for this station that are NOT ready/delivered
        return Order::with(['items' => function ($query) {
                $query->whereIn('status', ['sent', 'preparing'])
                      ->whereHas('dish', fn ($q) => $q->where('kitchen_station', $this->station));
            }])
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
