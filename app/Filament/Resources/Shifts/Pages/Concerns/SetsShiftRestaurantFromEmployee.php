<?php

namespace App\Filament\Resources\Shifts\Pages\Concerns;

use App\Models\Employee;
use App\Models\Shift;
use App\Support\AdminRestaurantContext;
use Illuminate\Validation\ValidationException;

trait SetsShiftRestaurantFromEmployee
{
    protected function setShiftRestaurantFromEmployee(array $data, ?int $ignoreShiftId = null): array
    {
        if (empty($data['employee_id'])) {
            return $data;
        }

        $employeeRestaurantId = Employee::whereKey($data['employee_id'])->value('restaurant_id');

        if (! $employeeRestaurantId) {
            throw ValidationException::withMessages([
                'employee_id' => 'El empleado seleccionado no existe.',
            ]);
        }

        $expectedRestaurantId = AdminRestaurantContext::selectedId();

        if ($expectedRestaurantId && (int) $employeeRestaurantId !== (int) $expectedRestaurantId) {
            throw ValidationException::withMessages([
                'employee_id' => 'El empleado seleccionado no pertenece al restaurante activo.',
            ]);
        }

        $data['restaurant_id'] = $employeeRestaurantId;

        $this->validateShiftDoesNotOverlap($data, $ignoreShiftId);

        return $data;
    }

    protected function validateShiftDoesNotOverlap(array $data, ?int $ignoreShiftId = null): void
    {
        if (empty($data['employee_id']) || empty($data['date']) || empty($data['start_time']) || empty($data['end_time'])) {
            return;
        }

        $overlapExists = Shift::query()
            ->where('employee_id', $data['employee_id'])
            ->whereDate('date', $data['date'])
            ->when($ignoreShiftId, fn ($query) => $query->whereKeyNot($ignoreShiftId))
            ->where('start_time', '<', $data['end_time'])
            ->where('end_time', '>', $data['start_time'])
            ->exists();

        if ($overlapExists) {
            throw ValidationException::withMessages([
                'start_time' => 'Este empleado ya tiene un turno que se solapa en esa fecha y horario.',
                'end_time' => 'Este empleado ya tiene un turno que se solapa en esa fecha y horario.',
            ]);
        }
    }
}
