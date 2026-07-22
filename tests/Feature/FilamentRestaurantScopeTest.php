<?php

namespace Tests\Feature;

use App\Filament\Resources\Clockings\ClockingResource;
use App\Filament\Resources\Concerns\RestaurantFormScoping;
use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Modifiers\ModifierResource;
use App\Filament\Resources\Shifts\Pages\Concerns\SetsShiftRestaurantFromEmployee;
use App\Filament\Resources\RestaurantResource;
use App\Support\AdminRestaurantContext;
use App\Models\Clocking;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Modifier;
use App\Models\ModifierGroup;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class FilamentRestaurantScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_only_sees_records_from_own_restaurant(): void
    {
        [$restaurant, $otherRestaurant, $manager] = $this->makeTenantScenario();

        $ownCustomer = Customer::create([
            'restaurant_id' => $restaurant->id,
            'name'          => 'Cliente propio',
        ]);
        $foreignCustomer = Customer::create([
            'restaurant_id' => $otherRestaurant->id,
            'name'          => 'Cliente externo',
        ]);

        $this->actingAs($manager);

        $this->assertTrue(CustomerResource::getEloquentQuery()->whereKey($ownCustomer->id)->exists());
        $this->assertFalse(CustomerResource::getEloquentQuery()->whereKey($foreignCustomer->id)->exists());
    }

    public function test_manager_only_sees_own_restaurant_record(): void
    {
        [$restaurant, $otherRestaurant, $manager] = $this->makeTenantScenario();

        $this->actingAs($manager);

        $this->assertTrue(RestaurantResource::getEloquentQuery()->whereKey($restaurant->id)->exists());
        $this->assertFalse(RestaurantResource::getEloquentQuery()->whereKey($otherRestaurant->id)->exists());
    }

    public function test_manager_scope_applies_through_modifier_group_relation(): void
    {
        [$restaurant, $otherRestaurant, $manager] = $this->makeTenantScenario();

        $ownGroup = ModifierGroup::create([
            'restaurant_id' => $restaurant->id,
            'name'          => 'Extras propios',
        ]);
        $foreignGroup = ModifierGroup::create([
            'restaurant_id' => $otherRestaurant->id,
            'name'          => 'Extras externos',
        ]);

        $ownModifier = Modifier::create([
            'modifier_group_id' => $ownGroup->id,
            'name'              => 'Queso',
            'price'             => 1,
        ]);
        $foreignModifier = Modifier::create([
            'modifier_group_id' => $foreignGroup->id,
            'name'              => 'Trufa',
            'price'             => 5,
        ]);

        $this->actingAs($manager);

        $this->assertTrue(ModifierResource::getEloquentQuery()->whereKey($ownModifier->id)->exists());
        $this->assertFalse(ModifierResource::getEloquentQuery()->whereKey($foreignModifier->id)->exists());
    }

    public function test_manager_scope_applies_through_employee_relation(): void
    {
        [$restaurant, $otherRestaurant, $manager] = $this->makeTenantScenario();

        $ownEmployee = Employee::create([
            'restaurant_id' => $restaurant->id,
            'first_name'    => 'Ana',
            'last_name'     => 'Mesa',
        ]);
        $foreignEmployee = Employee::create([
            'restaurant_id' => $otherRestaurant->id,
            'first_name'    => 'Luis',
            'last_name'     => 'Barra',
        ]);

        $ownClocking = Clocking::create([
            'employee_id'    => $ownEmployee->id,
            'clocked_in_at'  => now(),
        ]);
        $foreignClocking = Clocking::create([
            'employee_id'    => $foreignEmployee->id,
            'clocked_in_at'  => now(),
        ]);

        $this->actingAs($manager);

        $this->assertTrue(ClockingResource::getEloquentQuery()->whereKey($ownClocking->id)->exists());
        $this->assertFalse(ClockingResource::getEloquentQuery()->whereKey($foreignClocking->id)->exists());
    }

    public function test_super_admin_can_see_all_restaurant_records(): void
    {
        [$restaurant, $otherRestaurant] = $this->makeTenantScenario();

        $superAdmin = User::create([
            'name'          => 'Super Admin',
            'email'         => 'super-admin@kitchenflow.test',
            'password'      => bcrypt('password'),
            'restaurant_id' => $restaurant->id,
        ]);
        $superAdmin->assignRole('super_admin');

        $ownCustomer = Customer::create([
            'restaurant_id' => $restaurant->id,
            'name'          => 'Cliente propio',
        ]);
        $foreignCustomer = Customer::create([
            'restaurant_id' => $otherRestaurant->id,
            'name'          => 'Cliente externo',
        ]);

        $this->actingAs($superAdmin);

        $this->assertTrue(CustomerResource::getEloquentQuery()->whereKey($ownCustomer->id)->exists());
        $this->assertTrue(CustomerResource::getEloquentQuery()->whereKey($foreignCustomer->id)->exists());
    }

    public function test_super_admin_can_scope_admin_panel_to_selected_restaurant(): void
    {
        [$restaurant, $otherRestaurant] = $this->makeTenantScenario();

        $superAdmin = User::create([
            'name'          => 'Super Admin Context',
            'email'         => 'super-admin-context@kitchenflow.test',
            'password'      => bcrypt('password'),
            'restaurant_id' => $restaurant->id,
        ]);
        $superAdmin->assignRole('super_admin');

        $ownCustomer = Customer::create([
            'restaurant_id' => $restaurant->id,
            'name'          => 'Cliente A',
        ]);
        $selectedCustomer = Customer::create([
            'restaurant_id' => $otherRestaurant->id,
            'name'          => 'Cliente B',
        ]);

        $this->actingAs($superAdmin)
            ->withSession([AdminRestaurantContext::SESSION_KEY => $otherRestaurant->id]);

        $this->assertFalse(CustomerResource::getEloquentQuery()->whereKey($ownCustomer->id)->exists());
        $this->assertTrue(CustomerResource::getEloquentQuery()->whereKey($selectedCustomer->id)->exists());
    }

    public function test_super_admin_can_update_and_clear_restaurant_context(): void
    {
        [$restaurant, $otherRestaurant] = $this->makeTenantScenario();

        $superAdmin = User::create([
            'name'          => 'Super Admin Selector',
            'email'         => 'super-admin-selector@kitchenflow.test',
            'password'      => bcrypt('password'),
            'restaurant_id' => $restaurant->id,
        ]);
        $superAdmin->assignRole('super_admin');

        $this->actingAs($superAdmin)
            ->post(route('admin.restaurant-context.update'), ['restaurant_id' => $otherRestaurant->id])
            ->assertRedirect();

        $this->assertSame($otherRestaurant->id, session(AdminRestaurantContext::SESSION_KEY));

        $this->post(route('admin.restaurant-context.update'), ['restaurant_id' => null])
            ->assertRedirect();

        $this->assertNull(session(AdminRestaurantContext::SESSION_KEY));
    }

    public function test_manager_cannot_update_admin_restaurant_context(): void
    {
        [$restaurant, $otherRestaurant, $manager] = $this->makeTenantScenario();

        $this->actingAs($manager)
            ->post(route('admin.restaurant-context.update'), ['restaurant_id' => $otherRestaurant->id])
            ->assertForbidden();

        $this->assertNull(session(AdminRestaurantContext::SESSION_KEY));
        $this->assertSame($restaurant->id, AdminRestaurantContext::selectedId());
    }

    public function test_manager_form_restaurant_options_are_limited_to_own_restaurant(): void
    {
        [$restaurant, $otherRestaurant, $manager] = $this->makeTenantScenario();

        $this->actingAs($manager);

        $options = RestaurantFormScoping::restaurantOptions();

        $this->assertTrue($options->has($restaurant->id));
        $this->assertFalse($options->has($otherRestaurant->id));
    }

    public function test_manager_form_data_forces_own_restaurant(): void
    {
        [$restaurant, $otherRestaurant, $manager] = $this->makeTenantScenario();

        $this->actingAs($manager);

        $data = RestaurantFormScoping::forceRestaurantOnFormData([
            'restaurant_id' => $otherRestaurant->id,
            'name' => 'Dato manipulado',
        ]);

        $this->assertSame($restaurant->id, $data['restaurant_id']);
    }

    public function test_super_admin_form_relationship_scope_is_unrestricted_without_selected_restaurant(): void
    {
        [$restaurant, $otherRestaurant] = $this->makeTenantScenario();

        $superAdmin = User::create([
            'name'          => 'Super Admin',
            'email'         => 'super-admin-form@kitchenflow.test',
            'password'      => bcrypt('password'),
            'restaurant_id' => $restaurant->id,
        ]);
        $superAdmin->assignRole('super_admin');

        $this->actingAs($superAdmin);

        $this->assertTrue(RestaurantFormScoping::scopeToRestaurant(Restaurant::query())->whereKey($restaurant->id)->exists());
        $this->assertTrue(RestaurantFormScoping::scopeToRestaurant(Restaurant::query())->whereKey($otherRestaurant->id)->exists());
    }

    public function test_shift_form_data_uses_selected_employee_restaurant(): void
    {
        [$restaurant, $otherRestaurant] = $this->makeTenantScenario();

        $superAdmin = User::create([
            'name'          => 'Super Admin Shifts',
            'email'         => 'super-admin-shifts@kitchenflow.test',
            'password'      => bcrypt('password'),
            'restaurant_id' => $restaurant->id,
        ]);
        $superAdmin->assignRole('super_admin');

        $employee = Employee::create([
            'restaurant_id' => $otherRestaurant->id,
            'first_name'    => 'Empleado',
            'last_name'     => 'Turnos',
        ]);

        $this->actingAs($superAdmin)
            ->withSession([AdminRestaurantContext::SESSION_KEY => $otherRestaurant->id]);

        $data = (new class {
            use SetsShiftRestaurantFromEmployee;

            public function apply(array $data): array
            {
                return $this->setShiftRestaurantFromEmployee($data);
            }
        })->apply([
            'restaurant_id' => $restaurant->id,
            'employee_id'   => $employee->id,
        ]);

        $this->assertSame($otherRestaurant->id, $data['restaurant_id']);
    }

    public function test_shift_form_data_rejects_employee_from_another_restaurant(): void
    {
        [$restaurant, $otherRestaurant, $manager] = $this->makeTenantScenario();

        $foreignEmployee = Employee::create([
            'restaurant_id' => $otherRestaurant->id,
            'first_name'    => 'Empleado',
            'last_name'     => 'Externo',
        ]);

        $this->actingAs($manager);

        $this->expectException(ValidationException::class);

        (new class {
            use SetsShiftRestaurantFromEmployee;

            public function apply(array $data): array
            {
                return $this->setShiftRestaurantFromEmployee($data);
            }
        })->apply([
            'restaurant_id' => $restaurant->id,
            'employee_id'   => $foreignEmployee->id,
        ]);
    }

    private function makeTenantScenario(): array
    {
        $restaurant = Restaurant::create(['name' => 'Restaurante A', 'slug' => 'restaurante-a']);
        $otherRestaurant = Restaurant::create(['name' => 'Restaurante B', 'slug' => 'restaurante-b']);

        $manager = User::create([
            'name'          => 'Manager A',
            'email'         => 'manager-a@kitchenflow.test',
            'password'      => bcrypt('password'),
            'restaurant_id' => $restaurant->id,
        ]);
        $manager->assignRole('manager');

        return [$restaurant, $otherRestaurant, $manager];
    }
}
