<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessoriesInventoryAdjustment extends TenantModel
{
    protected $fillable = [
        'tenant_id',
        'accessory_inventory_id',
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

    public function accessoryInventory(): BelongsTo
    {
        return $this->belongsTo(AccessoriesInventory::class, 'accessory_inventory_id');
    }

    public function adjustedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }
}
