<?php

namespace Database\Seeders;

use App\Models\Dish;
use App\Models\MenuCategory;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $restaurant = Restaurant::first();
        if (!$restaurant) return;

        // Create Zones
        $zone1 = Zone::create(['restaurant_id' => $restaurant->id, 'name' => 'Sala Principal', 'is_active' => true]);
        $zone2 = Zone::create(['restaurant_id' => $restaurant->id, 'name' => 'Terraza', 'is_active' => true]);

        // Create Tables
        Table::create(['restaurant_id' => $restaurant->id, 'zone_id' => $zone1->id, 'number' => '1', 'capacity' => 4, 'status' => 'available']);
        Table::create(['restaurant_id' => $restaurant->id, 'zone_id' => $zone1->id, 'number' => '2', 'capacity' => 4, 'status' => 'available']);
        Table::create(['restaurant_id' => $restaurant->id, 'zone_id' => $zone1->id, 'number' => '3', 'capacity' => 2, 'status' => 'available']);
        Table::create(['restaurant_id' => $restaurant->id, 'zone_id' => $zone2->id, 'number' => 'T1', 'capacity' => 6, 'status' => 'available']);
        Table::create(['restaurant_id' => $restaurant->id, 'zone_id' => $zone2->id, 'number' => 'T2', 'capacity' => 4, 'status' => 'available']);

        // Create Categories
        $catEntrantes = MenuCategory::create(['restaurant_id' => $restaurant->id, 'name' => 'Entrantes', 'sort_order' => 1, 'is_active' => true]);
        $catPrincipales = MenuCategory::create(['restaurant_id' => $restaurant->id, 'name' => 'Principales', 'sort_order' => 2, 'is_active' => true]);
        $catBebidas = MenuCategory::create(['restaurant_id' => $restaurant->id, 'name' => 'Bebidas', 'sort_order' => 3, 'is_active' => true]);

        // Create Dishes
        Dish::create([
            'restaurant_id' => $restaurant->id,
            'menu_category_id' => $catEntrantes->id,
            'name' => 'Nachos con Queso',
            'slug' => Str::slug('Nachos con Queso'),
            'price' => 8.50,
            'cost' => 2.50,
            'is_available' => true,
            'kitchen_station' => 'hot',
        ]);
        
        Dish::create([
            'restaurant_id' => $restaurant->id,
            'menu_category_id' => $catEntrantes->id,
            'name' => 'Ensalada César',
            'slug' => Str::slug('Ensalada César'),
            'price' => 9.00,
            'cost' => 3.00,
            'is_available' => true,
            'kitchen_station' => 'cold',
        ]);

        Dish::create([
            'restaurant_id' => $restaurant->id,
            'menu_category_id' => $catPrincipales->id,
            'name' => 'Hamburguesa Clásica',
            'slug' => Str::slug('Hamburguesa Clásica'),
            'price' => 12.50,
            'cost' => 4.00,
            'is_available' => true,
            'kitchen_station' => 'hot',
        ]);

        Dish::create([
            'restaurant_id' => $restaurant->id,
            'menu_category_id' => $catBebidas->id,
            'name' => 'Refresco de Cola',
            'slug' => Str::slug('Refresco de Cola'),
            'price' => 2.50,
            'cost' => 0.50,
            'is_available' => true,
            'kitchen_station' => 'bar',
        ]);
        
        Dish::create([
            'restaurant_id' => $restaurant->id,
            'menu_category_id' => $catBebidas->id,
            'name' => 'Cerveza Artesanal',
            'slug' => Str::slug('Cerveza Artesanal'),
            'price' => 4.00,
            'cost' => 1.20,
            'is_available' => true,
            'kitchen_station' => 'bar',
        ]);
    }
}
