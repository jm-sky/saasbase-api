<?php

namespace App\Domain\Approval\Models;

use App\Domain\Approval\Enums\ApprovalExecutionStatus;
use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\BaseModel;
use App\Domain\Expense\Models\Expense;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string                                   $id
 * @property string                                   $expense_id
 * @property string                                   $workflow_id
 * @property ?string                                  $current_step_id
 * @property ApprovalExecutionStatus                  $status
 * @property ?string                                  $initiator_id
 * @property ?Carbon                                  $started_at
 * @property ?Carbon                                  $completed_at
 * @property Carbon                                   $created_at
 * @property Carbon                                   $updated_at
 * @property Expense                                  $expense
 * @property ApprovalWorkflow                         $workflow
 * @property ?ApprovalWorkflowStep                    $currentStep
 * @property ?User                                    $initiator
 * @property Collection<int, ApprovalExpenseDecision> $decisions
 */
class ApprovalExpenseExecution extends BaseModel
{
    protected $fillable = [
        'expense_id',
        'workflow_id',
        'current_step_id',
        'status',
        'initiator_id',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'status'       => ApprovalExecutionStatus::class,
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => ApprovalExecutionStatus::PENDING,
    ];

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class, 'expense_id')->withoutGlobalScopes();
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(ApprovalWorkflow::class, 'workflow_id');
    }

    public function currentStep(): BelongsTo
    {
        return $this->belongsTo(ApprovalWorkflowStep::class, 'current_step_id');
    }

    public function decisions(): HasMany
    {
        return $this->hasMany(ApprovalExpenseDecision::class, 'execution_id');
    }

    /**
     * Scope for pending executions.
     */
    public function scopePending($query)
    {
        return $query->where('status', ApprovalExecutionStatus::PENDING);
    }

    /**
     * Scope for approved executions.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', ApprovalExecutionStatus::APPROVED);
    }

    /**
     * Scope for rejected executions.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', ApprovalExecutionStatus::REJECTED);
    }

    /**
     * Scope for cancelled executions.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', ApprovalExecutionStatus::CANCELLED);
    }

    /**
     * Check if the execution is complete.
     */
    public function isComplete(): bool
    {
        return $this->status->isComplete();
    }

    /**
     * Check if the execution is pending.
     */
    public function isPending(): bool
    {
        return $this->status->isPending();
    }

    /**
     * Check if the execution was approved.
     */
    public function isApproved(): bool
    {
        return $this->status->isApproved();
    }

    /**
     * Check if the execution was rejected.
     */
    public function isRejected(): bool
    {
        return $this->status->isRejected();
    }

    /**
     * Get the total duration of the approval process in seconds.
     */
    public function getDurationInSeconds(): ?int
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        return (int) $this->completed_at->diffInSeconds($this->started_at);
    }
}
