<?php

namespace App\Filament\Resources\Shifts\Pages\Concerns;

use App\Models\Employee;

trait SetsShiftRestaurantFromEmployee
{
    protected function setShiftRestaurantFromEmployee(array $data): array
    {
        if (! empty($data['employee_id'])) {
            $employeeRestaurantId = Employee::whereKey($data['employee_id'])->value('restaurant_id');

            if ($employeeRestaurantId) {
                $data['restaurant_id'] = $employeeRestaurantId;
            }
        }

        return $data;
    }
}
