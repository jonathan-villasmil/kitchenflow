<?php

namespace Tests\Feature;

use App\Livewire\Pos\PosTerminal;
use App\Models\Customer;
use App\Models\Dish;
use App\Models\MenuCategory;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

}
