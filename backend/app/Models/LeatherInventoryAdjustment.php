<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeatherInventoryAdjustment extends TenantModel
{
    protected $fillable = [
        'tenant_id',
        'leather_inventory_id',
        'adjustment_type',
        'quantity',
        'notes',
        'adjusted_by',
        'adjusted_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'adjusted_at' => 'datetime',
    ];

    public function leatherInventory(): BelongsTo
    {
        return $this->belongsTo(LeatherInventory::class);
    }

    public function adjustedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }
}
