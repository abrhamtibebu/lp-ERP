<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinishedGoodsAdjustment extends TenantModel
{
    protected $fillable = [
        'tenant_id',
        'finished_good_id',
        'adjustment_type',
        'quantity',
        'reason',
        'export_reference',
        'adjusted_by',
        'adjusted_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'adjusted_at' => 'datetime',
    ];

    public function finishedGood(): BelongsTo
    {
        return $this->belongsTo(FinishedGood::class);
    }

    public function adjustedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }
}
