<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashRegister extends Model
{
    protected $fillable = [
        'restaurant_id',
        'opened_by',
        'closed_by',
        'opening_amount',
        'closing_amount',
        'expected_amount',
        'opened_at',
        'closed_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'opening_amount' => 'decimal:2',
        'closing_amount' => 'decimal:2',
        'expected_amount' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function openedBy()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function transactions()
    {
        return $this->hasMany(CashRegisterTransaction::class);
    }
}
