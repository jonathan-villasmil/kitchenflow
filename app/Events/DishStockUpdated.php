<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Dish;

class DishStockUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $dish;
    public $status;
    public $portions;

    /**
     * Create a new event instance.
     */
    public function __construct(Dish $dish, string $status, ?int $portions = null)
    {
        $this->dish = $dish;
        $this->status = $status;
        $this->portions = $portions;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('restaurant.public.' . $this->dish->restaurant_id),
        ];
    }

    /**
     * Data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'dish_id' => $this->dish->id,
            'status' => $this->status,
            'portions' => $this->portions,
        ];
    }
}
