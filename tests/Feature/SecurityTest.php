<?php

namespace Tests\Feature;

use App\Livewire\Kitchen\KitchenDisplay;
use App\Livewire\Pos\PosTerminal;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    private Restaurant $restaurant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = Restaurant::create([
            'name' => 'Restaurante Test',
            'slug' => 'restaurante-test',
        ]);
    }

    private function makeUser(string $role): User
    {
        $user = User::create([
            'name'          => "Usuario {$role}",
            'email'         => "{$role}@security.test",
            'password'      => bcrypt('password'),
            'restaurant_id' => $this->restaurant->id,
        ]);
        $user->assignRole($role);
        return $user;
    }

    // ─── POS Terminal ────────────────────────────────────────────────────────

    public function test_cocinero_cannot_access_pos_terminal(): void
    {
        $user = $this->makeUser('cocinero');

        Livewire::actingAs($user)
            ->test(PosTerminal::class)
            ->assertForbidden();
    }

    public function test_camarero_can_access_pos_terminal(): void
    {
        $user = $this->makeUser('camarero');

        Livewire::actingAs($user)
            ->test(PosTerminal::class)
            ->assertStatus(200);
    }

    public function test_cajero_can_access_pos_terminal(): void
    {
        $user = $this->makeUser('cajero');

        Livewire::actingAs($user)
            ->test(PosTerminal::class)
            ->assertStatus(200);
    }

    public function test_manager_can_access_pos_terminal(): void
    {
        $user = $this->makeUser('manager');

        Livewire::actingAs($user)
            ->test(PosTerminal::class)
            ->assertStatus(200);
    }

    // ─── Kitchen Display ─────────────────────────────────────────────────────

    public function test_camarero_cannot_access_kitchen_display(): void
    {
        $user = $this->makeUser('camarero');

        Livewire::actingAs($user)
            ->test(KitchenDisplay::class)
            ->assertForbidden();
    }

    public function test_cajero_cannot_access_kitchen_display(): void
    {
        $user = $this->makeUser('cajero');

        Livewire::actingAs($user)
            ->test(KitchenDisplay::class)
            ->assertForbidden();
    }

    public function test_cocinero_can_access_kitchen_display(): void
    {
        $user = $this->makeUser('cocinero');

        Livewire::actingAs($user)
            ->test(KitchenDisplay::class)
            ->assertStatus(200);
    }

    public function test_manager_can_access_kitchen_display(): void
    {
        $user = $this->makeUser('manager');

        Livewire::actingAs($user)
            ->test(KitchenDisplay::class)
            ->assertStatus(200);
    }

    // ─── Filament Admin Panel ─────────────────────────────────────────────────

    public function test_camarero_cannot_access_admin_panel(): void
    {
        $user = $this->makeUser('camarero');

        $this->actingAs($user)
            ->get('/admin')
            ->assertRedirectContains(route('pos'));
    }

    public function test_cocinero_cannot_access_admin_panel(): void
    {
        $user = $this->makeUser('cocinero');

        $this->actingAs($user)
            ->get('/admin')
            ->assertRedirectContains(route('kds'));
    }

    public function test_cajero_cannot_access_admin_panel(): void
    {
        $user = $this->makeUser('cajero');

        $this->actingAs($user)
            ->get('/admin')
            ->assertRedirectContains(route('pos'));
    }

    public function test_manager_can_access_admin_panel(): void
    {
        $user = $this->makeUser('manager');

        $this->actingAs($user)
            ->get('/admin')
            ->assertStatus(200);
    }

    // ─── Role redirection on root '/' ─────────────────────────────────────────

    public function test_camarero_is_redirected_to_pos_on_root(): void
    {
        $user = $this->makeUser('camarero');

        $this->actingAs($user)
            ->get('/')
            ->assertRedirect(route('pos'));
    }

    public function test_cajero_is_redirected_to_pos_on_root(): void
    {
        $user = $this->makeUser('cajero');

        $this->actingAs($user)
            ->get('/')
            ->assertRedirect(route('pos'));
    }

    public function test_cocinero_is_redirected_to_kds_on_root(): void
    {
        $user = $this->makeUser('cocinero');

        $this->actingAs($user)
            ->get('/')
            ->assertRedirect(route('kds'));
    }

    public function test_manager_is_redirected_to_admin_on_root(): void
    {
        $user = $this->makeUser('manager');

        $this->actingAs($user)
            ->get('/')
            ->assertRedirect('/admin');
    }
}
