<?php

namespace Database\Seeders;

use App\Models\Dish;
use App\Models\MenuCategory;
use App\Models\Modifier;
use App\Models\ModifierGroup;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\Zone;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\InventoryItem;
use App\Models\Employee;
use App\Models\Clocking;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ComprehensiveSeeder extends Seeder
{
    public function run(): void
    {
        $restaurant = Restaurant::first();
        if (!$restaurant) return;

        // ─── 1. Usuarios Adicionales ───────────────────────────────────
        $usersData = [
            ['first_name' => 'Juan', 'last_name' => 'Camarero', 'email' => 'juan@kitchenflow.com', 'role' => 'waiter', 'pin' => '1111'],
            ['first_name' => 'Sofia', 'last_name' => 'Chef', 'email' => 'sofia@kitchenflow.com', 'role' => 'kitchen', 'pin' => '2222'],
            ['first_name' => 'Pedro', 'last_name' => 'Cajero', 'email' => 'pedro@kitchenflow.com', 'role' => 'cashier', 'pin' => '3333'],
        ];

        foreach ($usersData as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['first_name'] . ' ' . $data['last_name'],
                    'password' => bcrypt('password'),
                    'restaurant_id' => $restaurant->id,
                    'pin' => $data['pin']
                ]
            );
            $user->assignRole($data['role']);

            // Crear registro de empleado vinculado
            Employee::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'restaurant_id' => $restaurant->id,
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'position' => $data['role'],
                    'is_active' => true
                ]
            );
        }

        // ─── 2. Modificadores ──────────────────────────────────────────
        $groupExtras = ModifierGroup::updateOrCreate(
            ['restaurant_id' => $restaurant->id, 'name' => 'Extras'],
            ['is_multiple_choice' => true, 'is_required' => false]
        );

        $modifiers = [
            ['name' => 'Extra Queso', 'price' => 1.50],
            ['name' => 'Bacon', 'price' => 2.00],
            ['name' => 'Huevo frito', 'price' => 1.20],
        ];

        foreach ($modifiers as $mod) {
            Modifier::updateOrCreate(
                ['modifier_group_id' => $groupExtras->id, 'name' => $mod['name']],
                ['price' => $mod['price']]
            );
        }

        $groupExclusiones = ModifierGroup::updateOrCreate(
            ['restaurant_id' => $restaurant->id, 'name' => 'Sin Ingredientes'],
            ['is_multiple_choice' => true, 'is_required' => false]
        );

        foreach (['Sin Cebolla', 'Sin Tomate', 'Sin Pepinillo'] as $exc) {
            Modifier::updateOrCreate(
                ['modifier_group_id' => $groupExclusiones->id, 'name' => $exc],
                ['price' => 0]
            );
        }

        // Vincular grupos a platos existentes (Hamburguesa)
        $hamburguesa = Dish::where('name', 'Hamburguesa Clásica')->first();
        if ($hamburguesa) {
            $hamburguesa->modifierGroups()->syncWithoutDetaching([$groupExtras->id, $groupExclusiones->id]);
        }

        // ─── 3. Inventario & Recetas ────────────────────────────────────
        $items = [
            ['name' => 'Carne de Vacuno (kg)', 'stock' => 50, 'unit' => 'kg', 'cost' => 8.50],
            ['name' => 'Pan Brioche (ud)', 'stock' => 100, 'unit' => 'ud', 'cost' => 0.40],
            ['name' => 'Queso Cheddar (kg)', 'stock' => 10, 'unit' => 'kg', 'cost' => 6.00],
        ];

        foreach ($items as $item) {
            InventoryItem::updateOrCreate(
                ['restaurant_id' => $restaurant->id, 'name' => $item['name']],
                [
                    'unit' => $item['unit'],
                    'stock_current' => $item['stock'],
                    'stock_minimum' => 5,
                    'cost_per_unit' => $item['cost'],
                    'track_stock' => true,
                    'is_active' => true
                ]
            );
        }

        // ─── 4. Recetas (Escandallos) ──────────────────────────────────
        if ($hamburguesa) {
            $carne = InventoryItem::where('name', 'Carne de Vacuno (kg)')->first();
            $pan = InventoryItem::where('name', 'Pan Brioche (ud)')->first();
            $queso = InventoryItem::where('name', 'Queso Cheddar (kg)')->first();

            if ($carne && $pan && $queso) {
                $hamburguesa->ingredients()->sync([
                    $carne->id => ['quantity' => 0.180], // 180g
                    $pan->id => ['quantity' => 1.000],   // 1 ud
                    $queso->id => ['quantity' => 0.040], // 40g
                ]);
            }
        }

        // ─── 5. Historial de Fichajes ───────────────────────────────────
        $juan = Employee::where('first_name', 'Juan')->first();
        if ($juan) {
            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                Clocking::create([
                    'employee_id' => $juan->id,
                    'clocked_in_at' => (clone $date)->setHour(9)->setMinute(0),
                    'clocked_out_at' => (clone $date)->setHour(17)->setMinute(0),
                    'total_minutes' => 480,
                    'notes' => 'Turno regular'
                ]);
            }
        }

        // ─── 6. Clientes (Para CRM) ───────────────────────────────────
        $customersData = [
            ['name' => 'Juan Pérez', 'email' => 'juanp@example.com', 'phone' => '600111222', 'points' => 150],
            ['name' => 'María García', 'email' => 'maria@example.com', 'phone' => '600333444', 'points' => 2500],
            ['name' => 'Carlos Rodríguez', 'email' => 'carlos@example.com', 'phone' => '600555666', 'points' => 0],
        ];

        foreach ($customersData as $cData) {
            \App\Models\Customer::updateOrCreate(
                ['email' => $cData['email']],
                [
                    'restaurant_id' => $restaurant->id,
                    'name' => $cData['name'],
                    'phone' => $cData['phone'],
                    'loyalty_points' => $cData['points']
                ]
            );
        }
    }
}
