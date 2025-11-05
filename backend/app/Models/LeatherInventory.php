<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeatherInventory extends TenantModel
{
    protected $fillable = [
        'tenant_id',
        'leather_name',
        'brand_make',
        'supplier_id',
        'purchase_date',
        'quantity_sqft',
        'consumption_reduction',
        'submitted_by',
        'received_by',
        'delivered_to',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'quantity_sqft' => 'decimal:2',
        'consumption_reduction' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function consumptionLogs(): HasMany
    {
        return $this->hasMany(LeatherConsumptionLog::class, 'leather_inventory_id');
    }
}

