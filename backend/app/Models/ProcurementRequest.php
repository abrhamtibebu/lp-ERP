<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProcurementRequest extends TenantModel
{
    protected $fillable = [
        'tenant_id',
        'request_number',
        'supplier_id',
        'request_date',
        'approved_date',
        'requested_by',
        'submitted_by',
        'received_by',
        'approved_by',
        'status',
        'notes',
    ];

    protected $casts = [
        'request_date' => 'date',
        'approved_date' => 'date',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProcurementRequestItem::class);
    }
}
