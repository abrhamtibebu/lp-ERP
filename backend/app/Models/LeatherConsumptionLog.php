<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeatherConsumptionLog extends TenantModel
{
    protected $fillable = [
        'tenant_id',
        'batch_id',
        'leather_inventory_id',
        'quantity_consumed',
        'consumption_type',
        'notes',
    ];

    protected $casts = [
        'quantity_consumed' => 'decimal:2',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function leatherInventory(): BelongsTo
    {
        return $this->belongsTo(LeatherInventory::class, 'leather_inventory_id');
    }
}

