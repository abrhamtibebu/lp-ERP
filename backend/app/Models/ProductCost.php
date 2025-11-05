<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductCost extends TenantModel
{
    protected $fillable = [
        'tenant_id',
        'product_id',
        'cost',
        'is_locked',
        'locked_by',
        'locked_at',
        'notes',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }
}

