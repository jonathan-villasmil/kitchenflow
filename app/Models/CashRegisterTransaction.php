<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashRegisterTransaction extends Model
{
    protected $fillable = [
        'cash_register_id',
        'user_id',
        'type',
        'amount',
        'payment_method',
        'reference_type',
        'reference_id',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }
}
