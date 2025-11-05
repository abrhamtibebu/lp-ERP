<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CommercialInvoice extends TenantModel
{
    protected $fillable = [
        'tenant_id',
        'order_id',
        'batch_id',
        'invoice_number',
        'product_details',
        'total_amount',
        'invoice_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'product_details' => 'array',
        'total_amount' => 'decimal:2',
        'invoice_date' => 'date',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(InvoiceAttachment::class);
    }

    public function revenue(): HasOne
    {
        return $this->hasOne(Revenue::class);
    }
}

