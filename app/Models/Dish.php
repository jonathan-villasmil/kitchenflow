<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dish extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id', 'menu_category_id', 'name', 'slug', 'description',
        'price', 'cost', 'image', 'sku', 'allergens', 'tags',
        'is_available', 'is_featured', 'preparation_time_minutes',
        'kitchen_station', 'sort_order',
    ];

    protected $casts = [
        'allergens' => 'array',
        'tags' => 'array',
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'is_available' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class, 'menu_category_id');
    }

    public function modifierGroups(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(ModifierGroup::class, 'dish_modifier_group', 'dish_id', 'modifier_group_id')->withTimestamps();
    }

    public function ingredients(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(InventoryItem::class, 'dish_ingredients')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    public function getMarginAttribute(): float
    {
        if (!$this->cost || $this->cost == 0) return 0;
        return (($this->price - $this->cost) / $this->price) * 100;
    }

    public function getDynamicPriceAttribute(): float
    {
        $basePrice = (float) $this->price;
        $lowestPrice = $basePrice;

        $happyHours = HappyHour::where('restaurant_id', $this->restaurant_id)
            ->where('is_active', true)
            ->get();

        foreach ($happyHours as $hh) {
            if (!$hh->isActiveNow()) continue;

            $applies = false;
            if ($hh->target_type === 'all') {
                $applies = true;
            } elseif ($hh->target_type === 'menu_category' && $hh->target_id === $this->menu_category_id) {
                $applies = true;
            } elseif ($hh->target_type === 'dish' && $hh->target_id === $this->id) {
                $applies = true;
            }

            if ($applies) {
                $discountedPrice = $basePrice * (1 - ($hh->discount_percentage / 100));
                if ($discountedPrice < $lowestPrice) {
                    $lowestPrice = $discountedPrice;
                }
            }
        }

        return round($lowestPrice, 2);
    }
}
