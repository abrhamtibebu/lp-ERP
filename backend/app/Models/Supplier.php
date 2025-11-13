<?php

namespace App\Models;

class Supplier extends TenantModel
{
    protected $fillable = [
        'tenant_id',
        'name',
        'tin_number',
        'business_number',
        'address',
        'woreda',
        'house_number',
        'phone_number',
        'products_supplied',
        'contact_info',
    ];

    public function leatherInventories()
    {
        return $this->hasMany(LeatherInventory::class);
    }
}

