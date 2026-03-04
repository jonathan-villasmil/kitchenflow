<?php

namespace Tests\Unit;

use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\Restaurant;
use App\Models\StockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_adds_on_in_movement(): void
    {
        $restaurant = Restaurant::create(['name' => 'Main Restaurant', 'slug' => 'main-restaurant']);
        $category = InventoryCategory::create(['restaurant_id' => $restaurant->id, 'name' => 'Vegetables']);
        
        $item = InventoryItem::create([
            'restaurant_id' => $restaurant->id,
            'inventory_category_id' => $category->id,
            'name' => 'Tomatoes',
            'unit' => 'kg',
            'stock_current' => 0,
            'track_stock' => true,
        ]);

        StockMovement::create([
            'restaurant_id' => $restaurant->id,
            'inventory_item_id' => $item->id,
            'type' => 'purchase',
            'quantity' => 10.5,
            'unit_cost' => 1.20,
        ]);

        $this->assertEquals(10.5, $item->fresh()->stock_current);
        $this->assertEquals(1.20, $item->fresh()->cost_per_unit);
    }

    public function test_stock_subtracts_on_out_movement(): void
    {
        $restaurant = Restaurant::create(['name' => 'Main Restaurant', 'slug' => 'main-restaurant']);
        $category = InventoryCategory::create(['restaurant_id' => $restaurant->id, 'name' => 'Vegetables']);
        
        $item = InventoryItem::create([
            'restaurant_id' => $restaurant->id,
            'inventory_category_id' => $category->id,
            'name' => 'Tomatoes',
            'unit' => 'kg',
            'stock_current' => 15,
            'track_stock' => true,
        ]);

        StockMovement::create([
            'restaurant_id' => $restaurant->id,
            'inventory_item_id' => $item->id,
            'type' => 'sale',
            'quantity' => 5,
        ]);

        $this->assertEquals(10, $item->fresh()->stock_current);
    }

    public function test_stock_sets_on_adjustment_movement(): void
    {
        $restaurant = Restaurant::create(['name' => 'Main Restaurant', 'slug' => 'main-restaurant']);
        $category = InventoryCategory::create(['restaurant_id' => $restaurant->id, 'name' => 'Vegetables']);
        
        $item = InventoryItem::create([
            'restaurant_id' => $restaurant->id,
            'inventory_category_id' => $category->id,
            'name' => 'Tomatoes',
            'unit' => 'kg',
            'stock_current' => 100,
            'track_stock' => true,
        ]);

        StockMovement::create([
            'restaurant_id' => $restaurant->id,
            'inventory_item_id' => $item->id,
            'type' => 'adjustment',
            'quantity' => 12.5,
        ]);

        $this->assertEquals(12.5, $item->fresh()->stock_current);
    }
}
