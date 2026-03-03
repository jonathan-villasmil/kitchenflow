<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Table extends Model
{
    use HasFactory;

    // Avoid conflict with Laravel's \Illuminate\Support\Facades\DB / Query Builder
    protected $table = 'tables';

    protected $fillable = [
        'restaurant_id', 'zone_id', 'number', 'capacity',
        'pos_x', 'pos_y', 'width', 'height', 'shape', 'status', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'pos_x' => 'float',
        'pos_y' => 'float',
        'capacity' => 'integer',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function activeOrder(): HasOne
    {
        return $this->hasOne(Order::class)->whereNotIn('status', ['paid', 'cancelled']);
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }
}
