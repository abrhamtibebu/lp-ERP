<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'tenant_id',
        'name',
        'address',
        'country',
        'email',
        'password',
        'department',
        'position',
        'employed_on',
        'emergency_contact',
        'preferences',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'employed_on' => 'date',
            'preferences' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')
            ->withPivot('tenant_id')
            ->withTimestamps();
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_user')
            ->withPivot('tenant_id')
            ->withTimestamps();
    }

    public function employeeDocuments(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function hasRole(string $roleName): bool
    {
        // Admin has all permissions
        if ($roleName === 'Admin') {
            return $this->roles()->where('name', 'Admin')->exists();
        }

        return $this->roles()->where('name', $roleName)->exists();
    }

    public function hasPermission(string $permissionName): bool
    {
        // Admin has all permissions
        if ($this->hasRole('Admin')) {
            return true;
        }

        // Check direct user permissions (with tenant scope)
        if ($this->permissions()
            ->where('name', $permissionName)
            ->wherePivot('tenant_id', $this->tenant_id)
            ->exists()) {
            return true;
        }

        // Check role-based permissions
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissionName) {
                $query->where('name', $permissionName);
            })
            ->exists();
    }

    public function getRolesForTenant(int $tenantId)
    {
        return $this->roles()
            ->wherePivot('tenant_id', $tenantId)
            ->get();
    }
}
