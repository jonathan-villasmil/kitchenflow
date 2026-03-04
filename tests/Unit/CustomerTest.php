<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_be_created_with_loyalty_points(): void
    {
        $restaurant = Restaurant::create(['name' => 'Main Restaurant', 'slug' => 'main-restaurant']);
        
        $customer = Customer::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Juan Garcia',
            'email' => 'juan@example.com',
            'phone' => '123456789',
            'loyalty_points' => 150,
        ]);

        $this->assertEquals(150, $customer->fresh()->loyalty_points);
        $this->assertEquals('juan@example.com', $customer->fresh()->email);
    }
}
