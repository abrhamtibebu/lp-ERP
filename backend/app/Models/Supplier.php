<?php

namespace App\Models;

class Supplier extends TenantModel
{
    protected $fillable = [
        'tenant_id',
        'name',
        'tin_number',
        'products_supplied',
        'contact_info',
    ];

    public function leatherInventories()
    {
        return $this->hasMany(LeatherInventory::class);
    }
}

