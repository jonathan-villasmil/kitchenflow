<?php

namespace Tests\Feature;

use App\Events\OrderUpdatedForKitchen;
use App\Events\OrderSentToKitchen;
use App\Livewire\Pos\PosTerminal;
use App\Models\Customer;
use App\Models\Dish;
use App\Models\MenuCategory;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderItem;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Tests\TestCase;

class PosTerminalTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_terminal_renders_for_authenticated_users(): void
    {
        $restaurant = Restaurant::create(['name' => 'Main Restaurant', 'slug' => 'main-restaurant']);
        $user = User::create([
            'name'          => 'Test POS User',
            'email'         => 'pos@kitchenflow.test',
            'password'      => bcrypt('password'),
            'restaurant_id' => $restaurant->id,
        ]);
        $user->assignRole('camarero');

        Livewire::actingAs($user)
            ->test(PosTerminal::class)
            ->assertStatus(200);
    }

    public function test_pos_terminal_redirects_unauthenticated_users(): void
    {
        $this->get('/pos')->assertStatus(302)->assertRedirect(route('login'));
    }

    public function test_waiter_can_login_with_hashed_pin(): void
    {
        $restaurant = Restaurant::create(['name' => 'Main Restaurant', 'slug' => 'main-restaurant']);
        $manager = User::create([
            'name'          => 'Manager',
            'email'         => 'manager@kitchenflow.test',
            'password'      => bcrypt('password'),
            'restaurant_id' => $restaurant->id,
        ]);
        $manager->assignRole('manager');

        $waiter = User::create([
            'name'          => 'Waiter',
            'email'         => 'waiter@kitchenflow.test',
            'password'      => bcrypt('password'),
            'restaurant_id' => $restaurant->id,
            'pin'           => '1234', // automatically hashed via cast
        ]);
        $waiter->assignRole('camarero');

        Livewire::actingAs($manager)
            ->test(PosTerminal::class)
            ->set('enteredPin', '1234')
            ->call('verifyPin')
            ->assertRedirect(route('pos'));

        $this->assertEquals($waiter->id, auth()->id());
    }

    public function test_seamless_pin_upgrade_migration(): void
    {
        $restaurant = Restaurant::create(['name' => 'Main Restaurant', 'slug' => 'main-restaurant']);
        $manager = User::create([
            'name'          => 'Manager',
            'email'         => 'manager@kitchenflow.test',
            'password'      => bcrypt('password'),
            'restaurant_id' => $restaurant->id,
        ]);
        $manager->assignRole('manager');

        // Manually insert raw plain text PIN bypassing Eloquent casts
        \DB::table('users')->insert([
            'name'          => 'Old Waiter',
            'email'         => 'old_waiter@kitchenflow.test',
            'password'      => bcrypt('password'),
            'restaurant_id' => $restaurant->id,
            'pin'           => '9999',
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        $waiter = User::where('email', 'old_waiter@kitchenflow.test')->first();
        $waiter->assignRole('camarero');
        $this->assertEquals('9999', $waiter->getRawOriginal('pin')); // verify raw plain text

        Livewire::actingAs($manager)
            ->test(PosTerminal::class)
            ->set('enteredPin', '9999')
            ->call('verifyPin')
            ->assertRedirect(route('pos'));

        $this->assertEquals($waiter->id, auth()->id());

        // Verify transparent auto-upgrade to secure hash
        $waiter->refresh();
        $this->assertNotEquals('9999', $waiter->getRawOriginal('pin'));
        $this->assertTrue(\Hash::check('9999', $waiter->pin));
    }

    public function test_pos_cannot_select_table_from_another_restaurant(): void
    {
        [$restaurant, $otherRestaurant, $user] = $this->makeTenantScenario();

        $foreignTable = Table::create([
            'restaurant_id' => $otherRestaurant->id,
            'number'        => 'B1',
            'capacity'      => 4,
            'is_active'     => true,
        ]);

        Livewire::actingAs($user)
            ->test(PosTerminal::class)
            ->call('selectTable', $foreignTable->id)
            ->assertForbidden();
    }

    public function test_pos_cannot_select_customer_from_another_restaurant(): void
    {
        [$restaurant, $otherRestaurant, $user] = $this->makeTenantScenario();

        $foreignCustomer = Customer::create([
            'restaurant_id'  => $otherRestaurant->id,
            'name'           => 'Cliente Externo',
            'loyalty_points' => 50,
        ]);

        Livewire::actingAs($user)
            ->test(PosTerminal::class)
            ->call('selectCustomer', $foreignCustomer->id)
            ->assertForbidden();
    }

    public function test_pos_cannot_send_foreign_dish_to_kitchen(): void
    {
        [$restaurant, $otherRestaurant, $user] = $this->makeTenantScenario();

        $table = Table::create([
            'restaurant_id' => $restaurant->id,
            'number'        => 'A1',
            'capacity'      => 4,
            'is_active'     => true,
        ]);

        $foreignCategory = MenuCategory::create([
            'restaurant_id' => $otherRestaurant->id,
            'name'          => 'Carta externa',
            'sort_order'    => 1,
            'is_active'     => true,
        ]);

        $foreignDish = Dish::create([
            'restaurant_id'    => $otherRestaurant->id,
            'menu_category_id' => $foreignCategory->id,
            'name'             => 'Plato externo',
            'slug'             => 'plato-externo',
            'price'            => 10,
            'is_available'     => true,
            'kitchen_station'  => 'hot',
        ]);

        Livewire::actingAs($user)
            ->test(PosTerminal::class)
            ->set('selectedTableId', $table->id)
            ->set('cart', [
                'tampered' => [
                    'order_item_id' => null,
                    'dish_id'       => $foreignDish->id,
                    'name'          => $foreignDish->name,
                    'unit_price'    => 10,
                    'quantity'      => 1,
                    'notes'         => '',
                    'modifiers'     => [],
                    'course'        => 1,
                    'line_total'    => 10,
                ],
            ])
            ->call('sendToKitchen')
            ->assertForbidden();

        $this->assertDatabaseMissing('order_items', [
            'dish_id' => $foreignDish->id,
        ]);
        $this->assertDatabaseMissing('orders', [
            'table_id' => $table->id,
        ]);
    }

    public function test_pos_broadcasts_kitchen_update_when_sent_item_is_cancelled(): void
    {
        Event::fake([OrderUpdatedForKitchen::class]);

        [$restaurant, , $user] = $this->makeTenantScenario();

        $manager = User::create([
            'name' => 'Manager PIN',
            'email' => 'manager-pin@kitchenflow.test',
            'password' => bcrypt('password'),
            'restaurant_id' => $restaurant->id,
            'pin' => '2468',
        ]);
        $manager->assignRole('manager');

        $table = Table::create([
            'restaurant_id' => $restaurant->id,
            'number' => 'A1',
            'capacity' => 4,
            'status' => 'occupied',
            'is_active' => true,
        ]);

        $order = Order::create([
            'restaurant_id' => $restaurant->id,
            'table_id' => $table->id,
            'user_id' => $user->id,
            'type' => 'dine_in',
            'status' => 'confirmed',
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'dish_id' => null,
            'name' => 'Croquetas',
            'unit_price' => 8,
            'quantity' => 1,
            'total' => 8,
            'status' => 'sent',
            'course' => 1,
            'sent_at' => now(),
        ]);

        Livewire::actingAs($user)
            ->test(PosTerminal::class)
            ->set('selectedTableId', $table->id)
            ->set('currentOrderId', $order->id)
            ->set('cart', [
                'item_' . $item->id => [
                    'order_item_id' => $item->id,
                    'dish_id' => null,
                    'name' => 'Croquetas',
                    'unit_price' => 8,
                    'quantity' => 1,
                    'notes' => '',
                    'modifiers' => [],
                    'course' => 1,
                    'line_total' => 8,
                ],
            ])
            ->set('itemKeyToCancel', 'item_' . $item->id)
            ->set('cancellationPin', '2468')
            ->call('confirmCancellation');

        Event::assertDispatched(OrderUpdatedForKitchen::class, function (OrderUpdatedForKitchen $event) use ($order, $item) {
            return $event->order->id === $order->id
                && $event->item?->id === $item->id
                && $event->action === 'order_cancelled'
                && $event->broadcastOn()[0]->name === 'private-kitchen.' . $order->restaurant_id;
        });

        $this->assertDatabaseHas('order_items', [
            'id' => $item->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_pos_can_send_manual_delivery_order_to_kitchen(): void
    {
        Event::fake([OrderSentToKitchen::class]);

        [$restaurant, , $user] = $this->makeTenantScenario();
        $dish = $this->makeDish($restaurant);

        Livewire::actingAs($user)
            ->test(PosTerminal::class)
            ->call('startDirectOrder', 'manual_delivery')
            ->set('deliveryCustomerName', 'Cliente Delivery')
            ->set('deliveryCustomerPhone', '600111222')
            ->set('deliveryAddressLine', 'Calle Mayor 1')
            ->set('deliveryCity', 'Madrid')
            ->set('deliveryPostalCode', '28001')
            ->set('deliveryFee', 2.5)
            ->set('cart', [
                'new_' . $dish->id => [
                    'order_item_id' => null,
                    'dish_id' => $dish->id,
                    'name' => $dish->name,
                    'unit_price' => 10,
                    'quantity' => 1,
                    'notes' => '',
                    'modifiers' => [],
                    'course' => 1,
                    'line_total' => 10,
                ],
            ])
            ->call('sendToKitchen');

        $order = Order::firstOrFail();

        $this->assertSame('delivery', $order->type);
        $this->assertSame('manual_delivery', $order->source);
        $this->assertNull($order->table_id);
        $this->assertSame('pending', $order->delivery_status);
        $this->assertSame(2.5, (float) $order->delivery->delivery_fee);
        $this->assertSame('Calle Mayor 1', $order->delivery->address_line);

        Event::assertDispatched(OrderSentToKitchen::class);
    }

    public function test_platform_delivery_requires_external_reference(): void
    {
        Event::fake([OrderSentToKitchen::class]);

        [$restaurant, , $user] = $this->makeTenantScenario();
        $dish = $this->makeDish($restaurant);

        Livewire::actingAs($user)
            ->test(PosTerminal::class)
            ->call('startDirectOrder', 'glovo')
            ->set('deliveryCustomerName', 'Cliente Glovo')
            ->set('deliveryCustomerPhone', '600333444')
            ->set('cart', [
                'new_' . $dish->id => [
                    'order_item_id' => null,
                    'dish_id' => $dish->id,
                    'name' => $dish->name,
                    'unit_price' => 10,
                    'quantity' => 1,
                    'notes' => '',
                    'modifiers' => [],
                    'course' => 1,
                    'line_total' => 10,
                ],
            ])
            ->call('sendToKitchen');

        $this->assertDatabaseCount('orders', 0);
        Event::assertNotDispatched(OrderSentToKitchen::class);
    }

    public function test_pos_can_send_glovo_order_with_external_reference(): void
    {
        Event::fake([OrderSentToKitchen::class]);

        [$restaurant, , $user] = $this->makeTenantScenario();
        $dish = $this->makeDish($restaurant);

        Livewire::actingAs($user)
            ->test(PosTerminal::class)
            ->call('startDirectOrder', 'glovo')
            ->set('deliveryCustomerName', 'Cliente Glovo')
            ->set('deliveryCustomerPhone', '600333444')
            ->set('externalOrderId', 'GLOVO-123')
            ->set('platformFee', 3.25)
            ->set('cart', [
                'new_' . $dish->id => [
                    'order_item_id' => null,
                    'dish_id' => $dish->id,
                    'name' => $dish->name,
                    'unit_price' => 10,
                    'quantity' => 1,
                    'notes' => '',
                    'modifiers' => [],
                    'course' => 1,
                    'line_total' => 10,
                ],
            ])
            ->call('sendToKitchen');

        $order = Order::firstOrFail();

        $this->assertSame('glovo', $order->source);
        $this->assertSame('glovo', $order->external_platform);
        $this->assertSame('GLOVO-123', $order->external_order_id);
        $this->assertSame(3.25, (float) $order->delivery->platform_fee);

        Event::assertDispatched(OrderSentToKitchen::class, fn (OrderSentToKitchen $event) =>
            $event->order->id === $order->id
                && $event->broadcastWith()['source'] === 'glovo'
        );
    }

    private function makeTenantScenario(): array
    {
        $restaurant = Restaurant::create(['name' => 'Restaurante A', 'slug' => 'restaurante-a']);
        $otherRestaurant = Restaurant::create(['name' => 'Restaurante B', 'slug' => 'restaurante-b']);

        $user = User::create([
            'name'          => 'Waiter A',
            'email'         => 'waiter-a@kitchenflow.test',
            'password'      => bcrypt('password'),
            'restaurant_id' => $restaurant->id,
        ]);
        $user->assignRole('camarero');

        return [$restaurant, $otherRestaurant, $user];
    }

    private function makeDish(Restaurant $restaurant): Dish
    {
        $category = MenuCategory::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Carta',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        return Dish::create([
            'restaurant_id' => $restaurant->id,
            'menu_category_id' => $category->id,
            'name' => 'Burger',
            'slug' => 'burger-' . $restaurant->id,
            'price' => 10,
            'is_available' => true,
            'kitchen_station' => 'hot',
        ]);
    }

}
