<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HappyHour extends Model
{
    protected $fillable = [
        'restaurant_id',
        'name',
        'target_type',
        'target_id',
        'discount_percentage',
        'valid_days',
        'start_time',
        'end_time',
        'is_active',
    ];

    protected $casts = [
        'valid_days' => 'array',
        'discount_percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function isActiveNow(): bool
    {
        if (!$this->is_active) return false;

        $now = now();
        $currentDay = $now->dayOfWeek; // 0 (Sunday) to 6 (Saturday)
        $currentTime = $now->format('H:i:s');

        // Check day
        if (!in_array($currentDay, $this->valid_days ?? [])) {
            return false;
        }

        // Check time
        if ($currentTime >= $this->start_time && $currentTime <= $this->end_time) {
            return true;
        }

        return false;
    }
}
