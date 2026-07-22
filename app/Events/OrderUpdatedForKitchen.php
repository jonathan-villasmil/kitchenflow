<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderUpdatedForKitchen implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Order $order,
        public string $action,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('kitchen.' . $this->order->restaurant_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'order_id' => $this->order->id,
            'status' => $this->order->status,
            'table_number' => $this->order->table?->number ?? 'N/A',
        ];
    }
}
