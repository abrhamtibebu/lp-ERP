<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batch extends TenantModel
{
    protected $fillable = [
        'tenant_id',
        'order_id',
        'batch_id',
        'current_stage_id',
        'status',
        'total_quantity',
        'current_quantity',
    ];

    protected $casts = [
        'total_quantity' => 'integer',
        'current_quantity' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(ProductionStage::class, 'current_stage_id');
    }

    public function stageMovements(): HasMany
    {
        return $this->hasMany(BatchStageMovement::class);
    }

    public function wipInventories(): HasMany
    {
        return $this->hasMany(WipInventory::class);
    }

    public function leatherConsumptionLogs(): HasMany
    {
        return $this->hasMany(LeatherConsumptionLog::class);
    }

    public function accessoriesConsumptionLogs(): HasMany
    {
        return $this->hasMany(AccessoriesConsumptionLog::class);
    }

    public function finishedGoods(): HasMany
    {
        return $this->hasMany(FinishedGood::class);
    }

    public function commercialInvoices(): HasMany
    {
        return $this->hasMany(CommercialInvoice::class);
    }
}

