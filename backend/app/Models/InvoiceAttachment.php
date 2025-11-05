<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceAttachment extends TenantModel
{
    protected $fillable = [
        'tenant_id',
        'commercial_invoice_id',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
    ];

    public function commercialInvoice(): BelongsTo
    {
        return $this->belongsTo(CommercialInvoice::class);
    }
}

