<?php

namespace App\Models;

class MiscellaneousCost extends TenantModel
{
    protected $fillable = [
        'tenant_id',
        'description',
        'amount',
        'type',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];
}

