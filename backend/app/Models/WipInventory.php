<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WipInventory extends TenantModel
{
    protected $fillable = [
        'tenant_id',
        'batch_id',
        'stage_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(ProductionStage::class, 'stage_id');
    }
}

