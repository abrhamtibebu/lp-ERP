<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcurementRequestItem extends Model
{
    protected $fillable = [
        'procurement_request_id',
        'item_name',
        'quantity',
        'unit',
        'specifications',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function procurementRequest(): BelongsTo
    {
        return $this->belongsTo(ProcurementRequest::class);
    }
}
