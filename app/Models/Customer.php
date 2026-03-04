<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id', 'name', 'email', 'phone', 'birthday',
        'loyalty_points', 'notes',
    ];

    protected $casts = [
        'birthday' => 'date',
        'loyalty_points' => 'integer',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
