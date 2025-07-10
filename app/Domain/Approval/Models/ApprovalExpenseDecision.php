<?php

namespace App\Domain\Approval\Models;

use App\Domain\Approval\Enums\ApprovalDecision;
use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string                   $id
 * @property string                   $execution_id
 * @property string                   $step_id
 * @property string                   $approver_id
 * @property ApprovalDecision         $decision
 * @property ?string                  $reason
 * @property Carbon                   $decided_at
 * @property Carbon                   $created_at
 * @property Carbon                   $updated_at
 * @property ApprovalExpenseExecution $execution
 * @property ApprovalWorkflowStep     $step
 * @property User                     $approver
 */
class ApprovalExpenseDecision extends BaseModel
{
    protected $fillable = [
        'execution_id',
        'step_id',
        'approver_id',
        'decision',
        'reason',
        'decided_at',
    ];

    protected $casts = [
        'decision'   => ApprovalDecision::class,
        'decided_at' => 'datetime',
    ];

    public function execution(): BelongsTo
    {
        return $this->belongsTo(ApprovalExpenseExecution::class, 'execution_id');
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(ApprovalWorkflowStep::class, 'step_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function isApproval(): bool
    {
        return ApprovalDecision::APPROVED === $this->decision;
    }

    public function isRejection(): bool
    {
        return ApprovalDecision::REJECTED === $this->decision;
    }
}
