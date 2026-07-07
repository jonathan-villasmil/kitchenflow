<?php

namespace Tests\Feature;

use App\Models\CashRegister;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ZReportSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_cannot_download_z_report_from_another_restaurant(): void
    {
        [$restaurant, $otherRestaurant, $manager] = $this->makeTenantScenario('manager');
        $foreignRegister = $this->makeClosedRegister($otherRestaurant->id);

        $this->actingAs($manager)
            ->get(route('pos.z-report', $foreignRegister))
            ->assertForbidden();
    }

    public function test_cajero_cannot_download_z_report_from_another_restaurant(): void
    {
        [$restaurant, $otherRestaurant, $cashier] = $this->makeTenantScenario('cajero');
        $foreignRegister = $this->makeClosedRegister($otherRestaurant->id);

        $this->actingAs($cashier)
            ->get(route('pos.z-report', $foreignRegister))
            ->assertForbidden();
    }

    public function test_super_admin_can_download_z_report_from_another_restaurant(): void
    {
        [$restaurant, $otherRestaurant, $superAdmin] = $this->makeTenantScenario('super_admin');
        $foreignRegister = $this->makeClosedRegister($otherRestaurant->id);

        $this->actingAs($superAdmin)
            ->get(route('pos.z-report', $foreignRegister))
            ->assertOk();
    }

    public function test_manager_can_download_z_report_from_own_restaurant(): void
    {
        [$restaurant, $otherRestaurant, $manager] = $this->makeTenantScenario('manager');
        $ownRegister = $this->makeClosedRegister($restaurant->id);

        $this->actingAs($manager)
            ->get(route('pos.z-report', $ownRegister))
            ->assertOk();
    }

    private function makeTenantScenario(string $role): array
    {
        $restaurant = Restaurant::create(['name' => 'Restaurante A', 'slug' => 'restaurante-a']);
        $otherRestaurant = Restaurant::create(['name' => 'Restaurante B', 'slug' => 'restaurante-b']);

        $user = User::create([
            'name'          => "Usuario {$role}",
            'email'         => "{$role}@z-report.test",
            'password'      => bcrypt('password'),
            'restaurant_id' => $restaurant->id,
        ]);
        $user->assignRole($role);

        return [$restaurant, $otherRestaurant, $user];
    }

    private function makeClosedRegister(int $restaurantId): CashRegister
    {
        $operator = User::create([
            'name'          => "Operador {$restaurantId}",
            'email'         => "operator-{$restaurantId}@z-report.test",
            'password'      => bcrypt('password'),
            'restaurant_id' => $restaurantId,
        ]);

        return CashRegister::create([
            'restaurant_id'    => $restaurantId,
            'opened_by'        => $operator->id,
            'closed_by'        => $operator->id,
            'opening_amount'   => 100,
            'closing_amount'   => 150,
            'expected_amount'  => 150,
            'opened_at'        => now()->subHours(8),
            'closed_at'        => now(),
            'status'           => 'closed',
        ]);
    }
}
