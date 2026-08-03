<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDelivery extends Model
{
    protected $fillable = [
        'order_id',
        'customer_name',
        'customer_phone',
        'address_line',
        'city',
        'postal_code',
        'delivery_notes',
        'scheduled_at',
        'delivery_fee',
        'platform_fee',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'delivery_fee' => 'decimal:2',
        'platform_fee' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
