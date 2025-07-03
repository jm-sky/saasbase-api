<?php

namespace App\Domain\Approval\Models;

use App\Domain\Common\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string                                    $id
 * @property string                                    $workflow_id
 * @property int                                       $step_order
 * @property string                                    $name
 * @property bool                                      $require_all_approvers
 * @property int                                       $min_approvers
 * @property Carbon                                    $created_at
 * @property Carbon                                    $updated_at
 * @property ApprovalWorkflow                          $workflow
 * @property Collection<int, ApprovalStepApprover>     $approvers
 * @property Collection<int, ApprovalExpenseDecision>  $decisions
 * @property Collection<int, ApprovalExpenseExecution> $executions
 */
class ApprovalWorkflowStep extends BaseModel
{
    protected $fillable = [
        'workflow_id',
        'step_order',
        'name',
        'require_all_approvers',
        'min_approvers',
    ];

    protected $casts = [
        'step_order'            => 'integer',
        'require_all_approvers' => 'boolean',
        'min_approvers'         => 'integer',
    ];

    protected $attributes = [
        'require_all_approvers' => false,
        'min_approvers'         => 1,
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(ApprovalWorkflow::class, 'workflow_id');
    }

    public function approvers(): HasMany
    {
        return $this->hasMany(ApprovalStepApprover::class, 'step_id');
    }

    public function decisions(): HasMany
    {
        return $this->hasMany(ApprovalExpenseDecision::class, 'step_id');
    }

    public function executions(): HasMany
    {
        return $this->hasMany(ApprovalExpenseExecution::class, 'current_step_id');
    }

    /**
     * Scope to order by step order.
     */
    public function scopeByOrder($query)
    {
        return $query->orderBy('step_order');
    }

    /**
     * Get the next step in the workflow.
     */
    public function getNextStep(): ?self
    {
        return self::where('workflow_id', $this->workflow_id)
            ->where('step_order', '>', $this->step_order)
            ->orderBy('step_order')
            ->first()
        ;
    }

    /**
     * Get the previous step in the workflow.
     */
    public function getPreviousStep(): ?self
    {
        return self::where('workflow_id', $this->workflow_id)
            ->where('step_order', '<', $this->step_order)
            ->orderByDesc('step_order')
            ->first()
        ;
    }

    /**
     * Check if this is the first step in the workflow.
     */
    public function isFirstStep(): bool
    {
        return $this->step_order === $this->workflow->steps()->min('step_order');
    }

    /**
     * Check if this is the last step in the workflow.
     */
    public function isLastStep(): bool
    {
        return $this->step_order === $this->workflow->steps()->max('step_order');
    }
}
