<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionStage extends Model
{
    protected $fillable = [
        'name',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function batchMovements(): HasMany
    {
        return $this->hasMany(BatchStageMovement::class, 'to_stage_id');
    }

    public function wipInventories(): HasMany
    {
        return $this->hasMany(WipInventory::class, 'stage_id');
    }
}

