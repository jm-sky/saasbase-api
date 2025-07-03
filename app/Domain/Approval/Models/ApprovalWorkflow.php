<?php

namespace App\Domain\Approval\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\BaseModel;
use App\Domain\Financial\Casts\BigDecimalCast;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Brick\Math\BigDecimal;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string                                    $id
 * @property string                                    $tenant_id
 * @property string                                    $name
 * @property ?string                                   $description
 * @property ?BigDecimal                               $match_amount_min
 * @property ?BigDecimal                               $match_amount_max
 * @property ?array                                    $match_conditions
 * @property int                                       $priority
 * @property bool                                      $is_active
 * @property string                                    $created_by
 * @property Carbon                                    $created_at
 * @property Carbon                                    $updated_at
 * @property User                                      $creator
 * @property Collection<int, ApprovalWorkflowStep>     $steps
 * @property Collection<int, ApprovalExpenseExecution> $executions
 */
class ApprovalWorkflow extends BaseModel
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'match_amount_min',
        'match_amount_max',
        'match_conditions',
        'priority',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'match_amount_min' => BigDecimalCast::class,
        'match_amount_max' => BigDecimalCast::class,
        'match_conditions' => 'array',
        'priority'         => 'integer',
        'is_active'        => 'boolean',
    ];

    protected $attributes = [
        'priority'  => 0,
        'is_active' => true,
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ApprovalWorkflowStep::class, 'workflow_id')->orderBy('step_order');
    }

    public function executions(): HasMany
    {
        return $this->hasMany(ApprovalExpenseExecution::class, 'workflow_id');
    }

    /**
     * Scope to active workflows only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by priority (higher priority first).
     */
    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'desc');
    }

    /**
     * Check if workflow has any steps defined.
     */
    public function hasSteps(): bool
    {
        return $this->steps()->exists();
    }

    /**
     * Get the first step of the workflow.
     */
    public function getFirstStep(): ?ApprovalWorkflowStep
    {
        return $this->steps->first();
    }

    /**
     * Check if an amount matches this workflow's criteria.
     */
    public function matchesAmount(?BigDecimal $amount): bool
    {
        if (!$amount) {
            return true;
        }

        if ($this->match_amount_min && $amount->isLessThan($this->match_amount_min)) {
            return false;
        }

        if ($this->match_amount_max && $amount->isGreaterThan($this->match_amount_max)) {
            return false;
        }

        return true;
    }
}
