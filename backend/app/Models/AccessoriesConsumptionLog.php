<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessoriesConsumptionLog extends TenantModel
{
    protected $fillable = [
        'tenant_id',
        'batch_id',
        'accessory_inventory_id',
        'quantity_consumed',
        'notes',
    ];

    protected $casts = [
        'quantity_consumed' => 'decimal:2',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function accessoryInventory(): BelongsTo
    {
        return $this->belongsTo(AccessoriesInventory::class, 'accessory_inventory_id');
    }
}

