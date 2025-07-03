<?php

namespace App\Domain\Expense\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\AllocationContractType;
use App\Domain\Common\Models\AllocationEquipmentType;
use App\Domain\Common\Models\AllocationLocation;
use App\Domain\Common\Models\BaseModel;
use App\Domain\Expense\Enums\AllocationDimensionType;
use App\Domain\Financial\Models\AllocationCostType;
use App\Domain\Financial\Models\AllocationRelatedTransactionCategory;
use App\Domain\Financial\Models\AllocationRevenueType;
use App\Domain\Financial\Models\AllocationTransactionType;
use App\Domain\Products\Models\AllocationProductCategory;
use App\Domain\Projects\Models\Project;
use App\Domain\Tenant\Models\OrganizationUnit;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string                                                                                                                                                                                                                            $id
 * @property string                                                                                                                                                                                                                            $allocation_id
 * @property AllocationDimensionType                                                                                                                                                                                                           $dimension_type
 * @property string                                                                                                                                                                                                                            $dimension_id
 * @property Carbon                                                                                                                                                                                                                            $created_at
 * @property Carbon                                                                                                                                                                                                                            $updated_at
 * @property ExpenseAllocation                                                                                                                                                                                                                 $allocation
 * @property User|AllocationLocation|AllocationProductCategory|Project|AllocationRevenueType|AllocationTransactionType|AllocationCostType|OrganizationUnit|AllocationRelatedTransactionCategory|AllocationContractType|AllocationEquipmentType $dimensionable
 */
class AllocationDimension extends BaseModel
{
    protected $fillable = [
        'allocation_id',
        'dimension_type',
        'dimension_id',
    ];

    protected $casts = [
        'dimension_type' => AllocationDimensionType::class,
    ];

    public function allocation(): BelongsTo
    {
        return $this->belongsTo(ExpenseAllocation::class, 'allocation_id');
    }

    /**
     * Get the dimension entity (polymorphic relationship).
     */
    public function dimensionable(): MorphTo
    {
        return $this->morphTo('dimensionable', 'dimension_type', 'dimension_id');
    }

    /**
     * Get the morph class for the current dimension type.
     */
    public function getDimensionClass(): string
    {
        return $this->dimension_type->getMorphClass();
    }

    /**
     * Get the dimension label for display.
     */
    public function getDimensionLabel(): string
    {
        return $this->dimension_type->label();
    }

    /**
     * Get the dimension label in English for display.
     */
    public function getDimensionLabelEN(): string
    {
        return $this->dimension_type->labelEN();
    }

    /**
     * Check if this dimension can be configured per tenant.
     */
    public function isConfigurable(): bool
    {
        return $this->dimension_type->isConfigurable();
    }

    /**
     * Check if this dimension is always visible.
     */
    public function isAlwaysVisible(): bool
    {
        return $this->dimension_type->isAlwaysVisible();
    }
}
