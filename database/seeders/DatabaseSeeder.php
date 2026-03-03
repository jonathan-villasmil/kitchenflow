<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── 1. Crear roles ────────────────────────────────────────────
        $roles = [
            'super_admin' => 'Super Administrador',
            'manager'     => 'Gerente',
            'cashier'     => 'Cajero',
            'waiter'      => 'Camarero',
            'kitchen'     => 'Cocinero',
        ];

        foreach ($roles as $slug => $name) {
            Role::firstOrCreate(['name' => $slug, 'guard_name' => 'web']);
        }

        // ─── 2. Crear permisos ─────────────────────────────────────────
        $permissions = [
            // Restaurante
            'view_any_restaurant', 'view_restaurant', 'create_restaurant', 'update_restaurant', 'delete_restaurant',
            // Mesas
            'view_any_table', 'view_table', 'create_table', 'update_table', 'delete_table',
            // Reservas
            'view_any_reservation', 'view_reservation', 'create_reservation', 'update_reservation', 'delete_reservation',
            // Menú
            'view_any_dish', 'view_dish', 'create_dish', 'update_dish', 'delete_dish',
            // Pedidos
            'view_any_order', 'view_order', 'create_order', 'update_order', 'delete_order',
            // Cocina
            'view_kitchen', 'update_kitchen_ticket',
            // Inventario
            'view_any_inventory', 'create_inventory', 'update_inventory', 'delete_inventory',
            // Empleados
            'view_any_employee', 'create_employee', 'update_employee', 'delete_employee',
            // Usuarios
            'view_any_user', 'create_user', 'update_user', 'delete_user',
            // Reportes
            'view_reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // ─── 3. Asignar permisos a roles ───────────────────────────────
        $superAdmin = Role::findByName('super_admin');
        $superAdmin->givePermissionTo(Permission::all());

        $manager = Role::findByName('manager');
        $manager->givePermissionTo([
            'view_any_restaurant', 'view_restaurant',
            'view_any_table', 'view_table', 'create_table', 'update_table',
            'view_any_reservation', 'view_reservation', 'create_reservation', 'update_reservation', 'delete_reservation',
            'view_any_dish', 'view_dish', 'create_dish', 'update_dish', 'delete_dish',
            'view_any_order', 'view_order', 'create_order', 'update_order',
            'view_kitchen', 'update_kitchen_ticket',
            'view_any_inventory', 'create_inventory', 'update_inventory',
            'view_any_employee', 'create_employee', 'update_employee',
            'view_any_user', 'create_user', 'update_user',
            'view_reports',
        ]);

        $cashier = Role::findByName('cashier');
        $cashier->givePermissionTo([
            'view_any_table', 'view_table', 'update_table',
            'view_any_reservation', 'view_reservation', 'create_reservation', 'update_reservation',
            'view_any_order', 'view_order', 'create_order', 'update_order',
            'view_any_dish', 'view_dish',
        ]);

        $waiter = Role::findByName('waiter');
        $waiter->givePermissionTo([
            'view_any_table', 'view_table', 'update_table',
            'view_any_reservation', 'view_reservation', 'create_reservation', 'update_reservation',
            'view_any_order', 'view_order', 'create_order', 'update_order',
            'view_any_dish', 'view_dish',
        ]);

        $kitchen = Role::findByName('kitchen');
        $kitchen->givePermissionTo([
            'view_kitchen', 'update_kitchen_ticket',
            'view_any_order', 'view_order',
        ]);

        // ─── 4. Crear restaurante demo ─────────────────────────────────
        $restaurant = Restaurant::firstOrCreate(
            ['slug' => 'restaurante-demo'],
            [
                'name'     => 'Restaurante Demo',
                'address'  => 'Calle Principal 1, Madrid',
                'phone'    => '+34 912 345 678',
                'email'    => 'info@restaurantedemo.com',
                'timezone' => 'Europe/Madrid',
                'currency' => 'EUR',
                'tax_rate' => '10.00',
            ]
        );

        // ─── 5. Crear usuario administrador ───────────────────────────
        $admin = User::firstOrCreate(
            ['email' => 'admin@kitchenflow.com'],
            [
                'name'          => 'Administrador',
                'password'      => bcrypt('password'),
                'restaurant_id' => $restaurant->id,
            ]
        );
        $admin->assignRole('super_admin');

        // ─── 6. Crear usuario manager demo ────────────────────────────
        $manager_user = User::firstOrCreate(
            ['email' => 'manager@kitchenflow.com'],
            [
                'name'          => 'Manager Demo',
                'password'      => bcrypt('password'),
                'restaurant_id' => $restaurant->id,
            ]
        );
        $manager_user->assignRole('manager');
    }
}
