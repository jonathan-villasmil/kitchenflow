<?php

namespace Tests\Feature;

use App\Events\OrderPaid;
use App\Events\OrderReadyForPickup;
use App\Events\OrderSentToKitchen;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class WebSocketsBroadcastingTest extends TestCase
{
    use RefreshDatabase;

    private Restaurant $restaurant1;
    private Restaurant $restaurant2;
    private User $userFromRestaurant1;
    private User $userFromRestaurant2;
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant1 = Restaurant::create(['name' => 'Restaurante 1', 'slug' => 'restaurante-1']);
        $this->restaurant2 = Restaurant::create(['name' => 'Restaurante 2', 'slug' => 'restaurante-2']);

        $this->userFromRestaurant1 = User::create([
            'name' => 'Empleado R1',
            'email' => 'r1@kitchenflow.test',
            'password' => bcrypt('password'),
            'restaurant_id' => $this->restaurant1->id,
        ]);

        $this->userFromRestaurant2 = User::create([
            'name' => 'Empleado R2',
            'email' => 'r2@kitchenflow.test',
            'password' => bcrypt('password'),
            'restaurant_id' => $this->restaurant2->id,
        ]);

        $this->order = Order::create([
            'restaurant_id' => $this->restaurant1->id,
            'type' => 'dine_in',
            'status' => 'pending',
            'guests' => 2,
        ]);
    }

    /**
     * Test that events are configured to broadcast on the correct PrivateChannels.
     */
    public function test_events_broadcast_on_correct_private_channels(): void
    {
        $sentEvent = new OrderSentToKitchen($this->order);
        $this->assertEquals('private-kitchen.' . $this->restaurant1->id, $sentEvent->broadcastOn()[0]->name);

        $pickupEvent = new OrderReadyForPickup($this->order);
        $this->assertEquals('private-restaurant.' . $this->restaurant1->id, $pickupEvent->broadcastOn()[0]->name);

        $paidEvent = new OrderPaid($this->order);
        $this->assertEquals('private-restaurant.' . $this->restaurant1->id, $paidEvent->broadcastOn()[0]->name);
    }

    /**
     * Test authorization on kitchen channel: same restaurant allowed, different restaurant denied.
     */
    public function test_kitchen_channel_authorization_security_rules(): void
    {
        // Act as user from same restaurant - should be authorized (HTTP 200)
        $response = $this->actingAs($this->userFromRestaurant1)
            ->postJson('/broadcasting/auth', [
                'channel_name' => 'private-kitchen.' . $this->restaurant1->id,
                'socket_id' => '1234.5678',
            ]);
        $response->assertStatus(200);

        // Act as user from different restaurant - should be denied (HTTP 403)
        $response = $this->actingAs($this->userFromRestaurant2)
            ->postJson('/broadcasting/auth', [
                'channel_name' => 'private-kitchen.' . $this->restaurant1->id,
                'socket_id' => '1234.5678',
            ]);
        dd($response->getStatusCode(), $response->getContent());
        $response->assertStatus(403);
    }

    /**
     * Test authorization on restaurant channel: same restaurant allowed, different restaurant denied.
     */
    public function test_restaurant_channel_authorization_security_rules(): void
    {
        // Act as user from same restaurant - should be authorized (HTTP 200)
        $response = $this->actingAs($this->userFromRestaurant1)
            ->postJson('/broadcasting/auth', [
                'channel_name' => 'private-restaurant.' . $this->restaurant1->id,
                'socket_id' => '1234.5678',
            ]);
        $response->assertStatus(200);

        // Act as user from different restaurant - should be denied (HTTP 403)
        $response = $this->actingAs($this->userFromRestaurant2)
            ->postJson('/broadcasting/auth', [
                'channel_name' => 'private-restaurant.' . $this->restaurant1->id,
                'socket_id' => '1234.5678',
            ]);
        $response->assertStatus(403);
    }
}
