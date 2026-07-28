<?php

namespace Tests\Feature;

use App\Livewire\Pos\PosTerminal;
use App\Models\Clocking;
use App\Models\Employee;
use App\Models\Restaurant;
use App\Models\Shift;
use App\Models\User;
use App\Support\ShiftClockingMatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class ShiftClockingLinkTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_pos_clocking_links_to_planned_shift_and_completes_it(): void
    {
        [$restaurant, $user, $employee] = $this->makeEmployeeWithUser();

        $shift = Shift::create([
            'restaurant_id' => $restaurant->id,
            'employee_id' => $employee->id,
            'date' => '2026-07-28',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'break_minutes' => 0,
            'status' => 'scheduled',
        ]);

        Carbon::setTestNow('2026-07-28 09:05:00');

        Livewire::actingAs($user)
            ->test(PosTerminal::class)
            ->call('fichar');

        $clocking = Clocking::firstOrFail();

        $this->assertSame($shift->id, $clocking->shift_id);
        $this->assertSame('confirmed', $shift->fresh()->status);
        $this->assertSame(5, $clocking->minutes_late);

        Carbon::setTestNow('2026-07-28 16:50:00');

        Livewire::actingAs($user)
            ->test(PosTerminal::class)
            ->call('fichar');

        $clocking->refresh();

        $this->assertSame('completed', $shift->fresh()->status);
        $this->assertSame(465.0, (float) $clocking->total_minutes);
        $this->assertSame(10, $clocking->minutes_early_departure);
        $this->assertSame('late_and_left_early', $clocking->attendance_status);
    }

    public function test_manual_clocking_data_links_to_matching_shift_and_calculates_total_minutes(): void
    {
        [$restaurant, , $employee] = $this->makeEmployeeWithUser();

        $shift = Shift::create([
            'restaurant_id' => $restaurant->id,
            'employee_id' => $employee->id,
            'date' => '2026-07-28',
            'start_time' => '10:00',
            'end_time' => '18:00',
            'break_minutes' => 0,
            'status' => 'scheduled',
        ]);

        $data = ShiftClockingMatcher::applyToClockingData([
            'employee_id' => $employee->id,
            'clocked_in_at' => '2026-07-28 10:00:00',
            'clocked_out_at' => '2026-07-28 18:15:00',
        ]);

        $this->assertSame($shift->id, $data['shift_id']);
        $this->assertSame(495.0, $data['total_minutes']);
    }

    public function test_overnight_shift_matches_clocking_after_midnight(): void
    {
        [$restaurant, , $employee] = $this->makeEmployeeWithUser();

        $shift = Shift::create([
            'restaurant_id' => $restaurant->id,
            'employee_id' => $employee->id,
            'date' => '2026-07-28',
            'start_time' => '22:00',
            'end_time' => '02:00',
            'break_minutes' => 0,
            'status' => 'scheduled',
        ]);

        $matchedShift = ShiftClockingMatcher::findForClockIn($employee->id, '2026-07-29 00:30:00');

        $this->assertSame($shift->id, $matchedShift?->id);
    }

    private function makeEmployeeWithUser(): array
    {
        $restaurant = Restaurant::create([
            'name' => 'Restaurante Turnos',
            'slug' => 'restaurante-turnos',
        ]);

        $user = User::create([
            'name' => 'Empleado Turnos',
            'email' => fake()->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'restaurant_id' => $restaurant->id,
        ]);
        $user->assignRole('camarero');

        $employee = Employee::create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id,
            'first_name' => 'Empleado',
            'last_name' => 'Turnos',
        ]);

        return [$restaurant, $user, $employee];
    }
}
