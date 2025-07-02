<?php

namespace App\Domain\Tenant\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\BaseModel;
use App\Domain\Tenant\Enums\UnitRoleLevel;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string                     $id
 * @property string                     $tenant_id
 * @property string                     $user_id
 * @property string                     $organization_unit_id
 * @property UnitRoleLevel              $role_level
 * @property bool                       $is_primary
 * @property Carbon                     $valid_from
 * @property ?Carbon                    $valid_until
 * @property Carbon                     $created_at
 * @property Carbon                     $updated_at
 * @property User                       $user
 * @property AllocationOrganizationUnit $organizationUnit
 */
class AllocationOrganizationUnitMembership extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'allocation_organization_unit_memberships';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'organization_unit_id',
        'role_level',
        'is_primary',
        'valid_from',
        'valid_until',
    ];

    protected $casts = [
        'role_level'  => UnitRoleLevel::class,
        'is_primary'  => 'boolean',
        'valid_from'  => 'datetime',
        'valid_until' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organizationUnit(): BelongsTo
    {
        return $this->belongsTo(AllocationOrganizationUnit::class, 'organization_unit_id');
    }

    public function isActive(): bool
    {
        $now = now();

        return $this->valid_from <= $now
               && (null === $this->valid_until || $this->valid_until >= $now);
    }
}
