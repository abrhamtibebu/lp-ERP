<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Advance extends TenantModel
{
    protected $fillable = [
        'tenant_id',
        'order_id',
        'user_id',
        'amount',
        'purpose',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

