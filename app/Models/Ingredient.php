<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ingredient extends Model
{
    protected $fillable = [
        'name',
        'unit',
        'stock',
        'min_stock',
        'cost_per_unit',
    ];

    protected $casts = [
        'stock'         => 'decimal:2',
        'min_stock'     => 'decimal:2',
        'cost_per_unit' => 'decimal:2',
    ];

    public function recipes(): HasMany
    {
        return $this->hasMany(ProductRecipe::class);
    }

    public function getStockStatusAttribute(): string
    {
        if ((float) $this->stock <= 0) {
            return 'habis';
        }
        if ((float) $this->stock <= (float) $this->min_stock) {
            return 'menipis';
        }
        return 'aman';
    }
}
