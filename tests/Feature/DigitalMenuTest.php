<?php

namespace Tests\Feature;

use App\Livewire\Public\DigitalMenu;
use App\Models\Dish;
use App\Models\MenuCategory;
use App\Models\Restaurant;
use App\Models\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DigitalMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_menu_cannot_add_dish_from_another_restaurant(): void
    {
        [$table, $foreignDish] = $this->makePublicMenuScenario();

        Livewire::withQueryParams(['hash' => $this->menuHash($table)])
            ->test(DigitalMenu::class, ['table' => $table])
            ->call('addToCart', $foreignDish->id)
            ->assertForbidden();
    }

    public function test_public_menu_cannot_submit_cart_with_foreign_dish(): void
    {
        [$table, $foreignDish] = $this->makePublicMenuScenario();

        Livewire::withQueryParams(['hash' => $this->menuHash($table)])
            ->test(DigitalMenu::class, ['table' => $table])
            ->set('cart', [
                'tampered' => [
                    'dish_id'    => $foreignDish->id,
                    'name'       => $foreignDish->name,
                    'unit_price' => 10,
                    'quantity'   => 1,
                    'notes'      => '',
                    'modifiers'  => [],
                    'line_total' => 10,
                ],
            ])
            ->call('submitOrder')
            ->assertForbidden();

        $this->assertDatabaseMissing('orders', [
            'table_id' => $table->id,
        ]);
        $this->assertDatabaseMissing('order_items', [
            'dish_id' => $foreignDish->id,
        ]);
    }

    private function makePublicMenuScenario(): array
    {
        $restaurant = Restaurant::create(['name' => 'Restaurante A', 'slug' => 'restaurante-a']);
        $otherRestaurant = Restaurant::create(['name' => 'Restaurante B', 'slug' => 'restaurante-b']);

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

        return [$table, $foreignDish];
    }

    private function menuHash(Table $table): string
    {
        return substr(md5('kitchenflow_' . $table->id . config('app.key')), 0, 10);
    }
}
