<?php

namespace App\Domain\Approval\Models;

use App\Domain\Approval\Enums\ApproverType;
use App\Domain\Common\Models\BaseModel;
use App\Domain\Tenant\Models\OrganizationUnit;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string               $id
 * @property string               $step_id
 * @property ApproverType         $approver_type
 * @property string               $approver_value
 * @property ?string              $organization_unit_id
 * @property bool                 $can_delegate
 * @property Carbon               $created_at
 * @property Carbon               $updated_at
 * @property ApprovalWorkflowStep $step
 * @property ?OrganizationUnit    $organizationUnit
 */
class ApprovalStepApprover extends BaseModel
{
    protected $fillable = [
        'step_id',
        'approver_type',
        'approver_value',
        'organization_unit_id',
        'can_delegate',
    ];

    protected $casts = [
        'approver_type' => ApproverType::class,
        'can_delegate'  => 'boolean',
    ];

    protected $attributes = [
        'can_delegate' => false,
    ];

    public function step(): BelongsTo
    {
        return $this->belongsTo(ApprovalWorkflowStep::class, 'step_id');
    }

    public function organizationUnit(): BelongsTo
    {
        return $this->belongsTo(OrganizationUnit::class, 'organization_unit_id');
    }

    /**
     * Scope by approver type.
     */
    public function scopeByType($query, ApproverType $type)
    {
        return $query->where('approver_type', $type);
    }

    /**
     * Scope for specific user approvers.
     */
    public function scopeUserApprovers($query)
    {
        return $query->where('approver_type', ApproverType::USER);
    }

    /**
     * Scope for unit role approvers.
     */
    public function scopeUnitRoleApprovers($query)
    {
        return $query->where('approver_type', ApproverType::UNIT_ROLE);
    }

    /**
     * Scope for system permission approvers.
     */
    public function scopeSystemPermissionApprovers($query)
    {
        return $query->where('approver_type', ApproverType::SYSTEM_PERMISSION);
    }

    /**
     * Check if this approver configuration is for a specific user.
     */
    public function isUserApprover(): bool
    {
        return ApproverType::USER === $this->approver_type;
    }

    /**
     * Check if this approver configuration is for a unit role.
     */
    public function isUnitRoleApprover(): bool
    {
        return ApproverType::UNIT_ROLE === $this->approver_type;
    }

    /**
     * Check if this approver configuration is for a system permission.
     */
    public function isSystemPermissionApprover(): bool
    {
        return ApproverType::SYSTEM_PERMISSION === $this->approver_type;
    }
}
