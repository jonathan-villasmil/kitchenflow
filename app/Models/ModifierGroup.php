<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModifierGroup extends Model
{
    protected $fillable = [
        'restaurant_id', 'name', 'is_multiple_choice', 'is_required'
    ];

    protected $casts = [
        'is_multiple_choice' => 'boolean',
        'is_required' => 'boolean',
    ];

    public function restaurant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function modifiers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Modifier::class);
    }

    public function dishes(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Dish::class, 'dish_modifier_group', 'modifier_group_id', 'dish_id')->withTimestamps();
    }
}
