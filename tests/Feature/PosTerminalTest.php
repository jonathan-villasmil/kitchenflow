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
            'name' => 'Test POS User',
            'email' => 'pos@kitchenflow.test',
            'password' => bcrypt('password'),
            'restaurant_id' => $restaurant->id,
        ]);

        Livewire::actingAs($user)
            ->test(PosTerminal::class)
            ->assertStatus(200);
    }

    public function test_pos_terminal_redirects_unauthenticated_users(): void
    {
        $this->get('/pos')->assertStatus(302)->assertRedirect(route('login'));
    }
}
