<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DishModifierGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'dish_id', 'name', 'required', 'multiple', 'min_select', 'max_select', 'sort_order',
    ];

    protected $casts = [
        'required' => 'boolean',
        'multiple' => 'boolean',
    ];

    public function dish(): BelongsTo
    {
        return $this->belongsTo(Dish::class);
    }

    public function modifiers(): HasMany
    {
        return $this->hasMany(DishModifier::class);
    }
}
