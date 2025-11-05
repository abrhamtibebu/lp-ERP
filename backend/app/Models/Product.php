<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends TenantModel
{
    protected $fillable = [
        'tenant_id',
        'product_name',
        'color',
        'sku',
        'weight_kg',
        'unit_price',
        'consumption_formula',
        'description',
    ];

    protected $casts = [
        'weight_kg' => 'decimal:2',
        'unit_price' => 'decimal:2',
    ];

    public function productCost(): HasOne
    {
        return $this->hasOne(ProductCost::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function finishedGoods(): HasMany
    {
        return $this->hasMany(FinishedGood::class);
    }
}

