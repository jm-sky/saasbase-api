<?php

namespace App\Domain\Tenant\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\BaseModel;
use App\Domain\Tenant\Enums\OrgUnitRole;
use App\Domain\Tenant\Enums\UnitRoleLevel;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string           $id
 * @property string           $tenant_id
 * @property string           $organization_unit_id
 * @property string           $user_id
 * @property OrgUnitRole      $role
 * @property ?UnitRoleLevel   $workflow_role_level
 * @property bool             $is_primary
 * @property Carbon           $valid_from
 * @property ?Carbon          $valid_until
 * @property Carbon           $created_at
 * @property Carbon           $updated_at
 * @property User             $user
 * @property OrganizationUnit $organizationUnit
 */
class OrgUnitUser extends BaseModel
{
    use HasFactory;
    use BelongsToTenant;

    protected $table = 'org_unit_user';

    protected $fillable = [
        'tenant_id',
        'organization_unit_id',
        'user_id',
        'role',
        'workflow_role_level',
        'is_primary',
        'valid_from',
        'valid_until',
    ];

    protected $casts = [
        'role'                => OrgUnitRole::class,
        'workflow_role_level' => UnitRoleLevel::class,
        'is_primary'          => 'boolean',
        'valid_from'          => 'datetime',
        'valid_until'         => 'datetime',
    ];

    protected $attributes = [
        'is_primary' => false,
        'valid_from' => 'now',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organizationUnit(): BelongsTo
    {
        return $this->belongsTo(OrganizationUnit::class);
    }

    /**
     * Check if this membership is currently active.
     */
    public function isActive(): bool
    {
        $now = now();

        return $this->valid_from <= $now
               && (null === $this->valid_until || $this->valid_until >= $now);
    }

    /**
     * Check if user can approve for another role level (for allocation workflows).
     */
    public function canApproveFor(?UnitRoleLevel $requestorLevel): bool
    {
        if (!$this->workflow_role_level || !$requestorLevel) {
            return false;
        }

        return $this->workflow_role_level->canApproveFor($requestorLevel);
    }

    /**
     * Scope to active memberships only.
     */
    public function scopeActive($query)
    {
        return $query->where('valid_from', '<=', now())
            ->where(function ($q) {
                $q->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', now())
                ;
            })
        ;
    }

    /**
     * Scope to primary memberships only.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope by workflow role level.
     */
    public function scopeWithWorkflowRole($query, UnitRoleLevel $roleLevel)
    {
        return $query->where('workflow_role_level', $roleLevel);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    // Enhanced isActive method to consider both date fields
    public function isActiveWithDates(): bool
    {
        $now = now();

        // Check validity period (existing logic)
        $validPeriodActive = $this->valid_from <= $now
                            && (null === $this->valid_until || $this->valid_until >= $now);

        // Check date range (new logic)
        $startOk = $this->start_date ? $this->start_date <= $now->toDateString() : true;
        $endOk   = $this->end_date ? $this->end_date >= $now->toDateString() : true;

        return $validPeriodActive && $startOk && $endOk;
    }

    // Scopes
    public function scopeWithPosition($query)
    {
        return $query->whereNotNull('position_id');
    }

    public function scopeWithoutPosition($query)
    {
        return $query->whereNull('position_id');
    }
}
