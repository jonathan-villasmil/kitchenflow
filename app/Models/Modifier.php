<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modifier extends Model
{
    protected $fillable = [
        'modifier_group_id', 'name', 'price'
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function modifierGroup(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ModifierGroup::class);
    }
}
