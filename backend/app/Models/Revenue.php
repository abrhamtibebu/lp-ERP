<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Revenue extends TenantModel
{
    protected $fillable = [
        'tenant_id',
        'commercial_invoice_id',
        'amount',
        'revenue_date',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'revenue_date' => 'date',
    ];

    public function commercialInvoice(): BelongsTo
    {
        return $this->belongsTo(CommercialInvoice::class);
    }
}

