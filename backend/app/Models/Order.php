<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends TenantModel
{
    protected $fillable = [
        'tenant_id',
        'product_id',
        'quantity',
        'color',
        'sku',
        'status',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function advances(): HasMany
    {
        return $this->hasMany(Advance::class);
    }

    public function commercialInvoices(): HasMany
    {
        return $this->hasMany(CommercialInvoice::class);
    }
}

