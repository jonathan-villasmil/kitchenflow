<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DishModifier extends Model
{
    use HasFactory;

    protected $fillable = [
        'dish_modifier_group_id', 'name', 'price_adjustment', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price_adjustment' => 'decimal:2',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(DishModifierGroup::class, 'dish_modifier_group_id');
    }
}
