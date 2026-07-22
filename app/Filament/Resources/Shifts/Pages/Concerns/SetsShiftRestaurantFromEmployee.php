<?php

namespace App\Filament\Resources\Shifts\Pages\Concerns;

use App\Models\Employee;
use App\Support\AdminRestaurantContext;
use Illuminate\Validation\ValidationException;

trait SetsShiftRestaurantFromEmployee
{
    protected function setShiftRestaurantFromEmployee(array $data): array
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

        return $data;
    }
}
