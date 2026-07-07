<?php

namespace Tests\Feature;

use App\Livewire\Pos\PosTerminal;
use App\Models\Restaurant;
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

}
