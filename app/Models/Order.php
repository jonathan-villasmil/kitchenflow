<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'number', 'restaurant_id', 'table_id', 'customer_id', 'user_id',
        'type', 'status', 'guests', 'subtotal', 'tax_amount',
        'discount_amount', 'total', 'notes', 'opened_at', 'closed_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->number = static::generateNumber($order->restaurant_id);
            $order->opened_at = now();
        });
    }

    public static function generateNumber(int $restaurantId): string
    {
        $count = static::where('restaurant_id', $restaurantId)->count() + 1;
        return str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function waiter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function cashRegisterTransactions(): MorphMany
    {
        return $this->morphMany(CashRegisterTransaction::class, 'reference');
    }

    public function recalculateTotals(): void
    {
        $subtotal = $this->items()
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        $taxRate = $this->restaurant->tax_rate / 100;

        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => $subtotal * $taxRate,
            'total' => $subtotal + ($subtotal * $taxRate) - $this->discount_amount,
        ]);
    }
}
