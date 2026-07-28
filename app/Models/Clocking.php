<?php

namespace App\Models;

use App\Support\ShiftClockingMatcher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Clocking extends Model
{
    protected $fillable = [
        'employee_id',
        'shift_id',
        'clocked_in_at',
        'clocked_out_at',
        'total_minutes',
        'notes',
    ];

    protected $casts = [
        'clocked_in_at' => 'datetime',
        'clocked_out_at' => 'datetime',
        'total_minutes' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function getMinutesLateAttribute(): int
    {
        return ShiftClockingMatcher::minutesLate($this);
    }

    public function getMinutesEarlyDepartureAttribute(): int
    {
        return ShiftClockingMatcher::minutesEarlyDeparture($this);
    }

    public function getAttendanceStatusAttribute(): string
    {
        return ShiftClockingMatcher::status($this);
    }
}
