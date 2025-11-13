<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccessoriesInventory extends TenantModel
{
    protected $table = 'accessories_inventory';

    protected $fillable = [
        'tenant_id',
        'name',
        'quantity',
        'low_stock_threshold',
        'unit',
        'import_invoice_number',
        'file_path',
        'submitted_by',
        'received_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'low_stock_threshold' => 'decimal:2',
    ];

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
        return $this->hasMany(AccessoriesConsumptionLog::class, 'accessory_inventory_id');
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(AccessoriesInventoryAdjustment::class, 'accessory_inventory_id');
    }
}

