<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id', 'table_id', 'customer_id', 'user_id',
        'guest_name', 'guest_phone', 'guest_email', 'party_size',
        'reserved_at', 'duration_minutes', 'status', 'notes', 'source',
    ];

    protected $casts = [
        'reserved_at' => 'datetime',
        'party_size' => 'integer',
        'duration_minutes' => 'integer',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
