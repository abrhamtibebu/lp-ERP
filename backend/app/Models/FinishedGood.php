<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinishedGood extends TenantModel
{
    protected $fillable = [
        'tenant_id',
        'batch_id',
        'product_id',
        'quantity',
        'completed_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'completed_at' => 'date',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

