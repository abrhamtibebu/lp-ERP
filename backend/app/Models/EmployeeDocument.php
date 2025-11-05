<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocument extends TenantModel
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'file_path',
        'file_name',
        'document_type',
        'mime_type',
        'file_size',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

