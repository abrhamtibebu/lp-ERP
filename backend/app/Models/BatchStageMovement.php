<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchStageMovement extends TenantModel
{
    protected $fillable = [
        'tenant_id',
        'batch_id',
        'from_stage_id',
        'to_stage_id',
        'quantity',
        'supervisor_id',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function fromStage(): BelongsTo
    {
        return $this->belongsTo(ProductionStage::class, 'from_stage_id');
    }

    public function toStage(): BelongsTo
    {
        return $this->belongsTo(ProductionStage::class, 'to_stage_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }
}

