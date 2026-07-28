<?php

namespace App\Support;

use App\Models\Clocking;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class ShiftClockingMatcher
{
    private const MATCH_GRACE_HOURS = 6;

    public static function findForClockIn(int $employeeId, mixed $clockedInAt): ?Shift
    {
        $clockIn = Carbon::parse($clockedInAt);

        return Shift::query()
            ->where('employee_id', $employeeId)
            ->whereBetween('date', [
                $clockIn->copy()->subDay()->toDateString(),
                $clockIn->copy()->addDay()->toDateString(),
            ])
            ->get()
            ->filter(function (Shift $shift) use ($clockIn) {
                [$start, $end] = self::plannedRange($shift);

                return $clockIn->betweenIncluded(
                    $start->copy()->subHours(self::MATCH_GRACE_HOURS),
                    $end->copy()->addHours(self::MATCH_GRACE_HOURS),
                );
            })
            ->sortBy(function (Shift $shift) use ($clockIn) {
                [$start] = self::plannedRange($shift);

                return abs($clockIn->diffInSeconds($start, false));
            })
            ->first();
    }

    public static function applyToClockingData(array $data): array
    {
        if (! Arr::get($data, 'employee_id') || ! Arr::get($data, 'clocked_in_at')) {
            return $data;
        }

        $shift = self::findForClockIn((int) $data['employee_id'], $data['clocked_in_at']);

        if ($shift && empty($data['shift_id'])) {
            $data['shift_id'] = $shift->id;
        }

        if (! empty($data['clocked_out_at'])) {
            $data['total_minutes'] = Carbon::parse($data['clocked_in_at'])
                ->diffInMinutes(Carbon::parse($data['clocked_out_at']));
        }

        return $data;
    }

    public static function plannedRange(Shift $shift): array
    {
        $date = Carbon::parse($shift->date)->toDateString();
        $start = Carbon::parse($date . ' ' . $shift->start_time);
        $end = Carbon::parse($date . ' ' . $shift->end_time);

        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        return [$start, $end];
    }

    public static function minutesLate(Clocking $clocking): int
    {
        if (! $clocking->shift || ! $clocking->clocked_in_at) {
            return 0;
        }

        [$start] = self::plannedRange($clocking->shift);

        return max(0, $start->diffInMinutes($clocking->clocked_in_at, false));
    }

    public static function minutesEarlyDeparture(Clocking $clocking): int
    {
        if (! $clocking->shift || ! $clocking->clocked_out_at) {
            return 0;
        }

        [, $end] = self::plannedRange($clocking->shift);

        return max(0, $clocking->clocked_out_at->diffInMinutes($end, false));
    }

    public static function status(Clocking $clocking): string
    {
        if (! $clocking->shift) {
            return 'unplanned';
        }

        if (! $clocking->clocked_out_at) {
            return self::minutesLate($clocking) > 0 ? 'open_late' : 'open';
        }

        $late = self::minutesLate($clocking) > 0;
        $early = self::minutesEarlyDeparture($clocking) > 0;

        return match (true) {
            $late && $early => 'late_and_left_early',
            $late => 'late',
            $early => 'left_early',
            default => 'matched',
        };
    }
}
