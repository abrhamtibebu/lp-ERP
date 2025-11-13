<?php

namespace App\Models;

class MiscellaneousCost extends TenantModel
{
    protected $fillable = [
        'tenant_id',
        'description',
        'amount',
        'currency',
        'type',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];
}

