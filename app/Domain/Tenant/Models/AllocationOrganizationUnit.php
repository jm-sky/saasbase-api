<?php

namespace App\Domain\Tenant\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\BaseModel;
use App\Domain\Tenant\Traits\IsGlobalOrBelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection as SupportCollection;

/**
 * @property string                                                $id
 * @property ?string                                               $tenant_id
 * @property string                                                $code
 * @property string                                                $name
 * @property ?string                                               $description
 * @property ?string                                               $parent_id
 * @property bool                                                  $is_active
 * @property Carbon                                                $created_at
 * @property Carbon                                                $updated_at
 * @property ?AllocationOrganizationUnit                           $parent
 * @property Collection<int, AllocationOrganizationUnit>           $children
 * @property Collection<int, AllocationOrganizationUnitMembership> $memberships
 * @property Collection<int, User>                                 $members
 */
class AllocationOrganizationUnit extends BaseModel
{
    use IsGlobalOrBelongsToTenant;

    protected $table = 'allocation_organization_units';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'description',
        'parent_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(AllocationOrganizationUnitMembership::class, 'organization_unit_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'allocation_organization_unit_memberships', 'organization_unit_id', 'user_id')
            ->withPivot(['role_level', 'is_primary', 'valid_from', 'valid_until'])
            ->withTimestamps()
        ;
    }

    public function getOwnersAttribute(): SupportCollection
    {
        return $this->memberships()
            ->where('role_level', 'unit-owner')
            ->where('valid_from', '<=', now())
            ->where(function ($query) {
                $query->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', now())
                ;
            })
            ->with('user')
            ->get()
            ->pluck('user')
        ;
    }
}
