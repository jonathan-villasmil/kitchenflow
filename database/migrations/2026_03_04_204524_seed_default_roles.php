<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $roles = [
            'super_admin',
            'manager',
            'cajero',
            'recepcion',
            'camarero',
            'cocinero',
            'limpieza'
        ];

        foreach ($roles as $role) {
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $admin = \App\Models\User::find(1);
        if ($admin) {
            $admin->assignRole('super_admin');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $roles = [
            'super_admin', 'manager', 'cajero', 'recepcion', 'camarero', 'cocinero', 'limpieza'
        ];
        
        \Spatie\Permission\Models\Role::whereIn('name', $roles)->delete();
    }
};
