<?php

namespace Tests\Feature;

use App\Filament\Resources\RestaurantResource;
use App\Filament\Resources\Restaurants\Pages\CreateRestaurant;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RestaurantCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_restaurant_without_typing_slug(): void
    {
        $superAdmin = $this->makeUser('super_admin');

        Livewire::actingAs($superAdmin)
            ->test(CreateRestaurant::class)
            ->fillForm([
                'name' => 'Nuevo Restaurante',
                'email' => 'nuevo@example.com',
                'phone' => '600000000',
                'address' => 'Calle Nueva 1',
                'tax_rate' => 10,
                'currency' => 'EUR',
                'loyalty_points_per_unit' => 1,
                'loyalty_redemption_rate' => 100,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('restaurants', [
            'name' => 'Nuevo Restaurante',
            'slug' => 'nuevo-restaurante',
        ]);
    }

    public function test_restaurant_slug_is_unique_when_name_repeats(): void
    {
        Restaurant::create([
            'name' => 'Nuevo Restaurante',
            'slug' => 'nuevo-restaurante',
        ]);

        $superAdmin = $this->makeUser('super_admin');

        Livewire::actingAs($superAdmin)
            ->test(CreateRestaurant::class)
            ->fillForm([
                'name' => 'Nuevo Restaurante',
                'tax_rate' => 10,
                'currency' => 'EUR',
                'loyalty_points_per_unit' => 1,
                'loyalty_redemption_rate' => 100,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('restaurants', [
            'name' => 'Nuevo Restaurante',
            'slug' => 'nuevo-restaurante-2',
        ]);
    }

    public function test_manager_cannot_create_restaurants(): void
    {
        $manager = $this->makeUser('manager');

        $this->actingAs($manager);

        $this->assertFalse(RestaurantResource::canCreate());
    }

    private function makeUser(string $role): User
    {
        $restaurant = Restaurant::create([
            'name' => "Restaurante {$role}",
            'slug' => "restaurante-{$role}",
        ]);

        $user = User::create([
            'name' => "Usuario {$role}",
            'email' => "{$role}@restaurant-create.test",
            'password' => bcrypt('password'),
            'restaurant_id' => $restaurant->id,
        ]);
        $user->assignRole($role);

        return $user;
    }
}
