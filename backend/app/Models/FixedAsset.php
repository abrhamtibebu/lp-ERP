<?php

namespace App\Models;

class FixedAsset extends TenantModel
{
    protected $fillable = [
        'tenant_id',
        'description',
        'category',
        'purchase_year',
        'purchase_value',
        'currency',
        'depreciation',
        'notes',
    ];

    protected $casts = [
        'purchase_year' => 'date',
        'purchase_value' => 'decimal:2',
        'depreciation' => 'decimal:2',
    ];
}

