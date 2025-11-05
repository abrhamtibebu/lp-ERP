<?php

namespace App\Models;

class FixedAsset extends TenantModel
{
    protected $fillable = [
        'tenant_id',
        'description',
        'purchase_year',
        'depreciation',
        'notes',
    ];

    protected $casts = [
        'purchase_year' => 'date',
        'depreciation' => 'decimal:2',
    ];
}

