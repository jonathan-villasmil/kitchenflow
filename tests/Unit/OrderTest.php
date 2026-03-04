<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_number_is_generated_on_creation(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Main Restaurant',
            'slug' => 'main-restaurant',
        ]);
        
        $order = Order::create([
            'restaurant_id' => $restaurant->id,
            'type' => 'dine_in',
            'status' => 'pending',
            'guests' => 2,
        ]);

        $this->assertNotNull($order->number);
        $this->assertEquals('00001', $order->number);

        $order2 = Order::create([
            'restaurant_id' => $restaurant->id,
            'type' => 'dine_in',
            'status' => 'pending',
            'guests' => 3,
        ]);

        $this->assertEquals('00002', $order2->number);
    }
}
