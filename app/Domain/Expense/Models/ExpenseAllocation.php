<?php

namespace App\Domain\Expense\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Expense\Enums\ExpenseAllocationStatus;
use App\Domain\Financial\Casts\BigDecimalCast;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Brick\Math\BigDecimal;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string                               $id
 * @property string                               $tenant_id
 * @property string                               $expense_id
 * @property BigDecimal                           $amount
 * @property ?string                              $note
 * @property ExpenseAllocationStatus              $status
 * @property Carbon                               $created_at
 * @property Carbon                               $updated_at
 * @property Expense                              $expense
 * @property Collection<int, AllocationDimension> $dimensions
 */
class ExpenseAllocation extends BaseModel
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'expense_id',
        'amount',
        'note',
        'status',
    ];

    protected $casts = [
        'amount' => BigDecimalCast::class,
        'status' => ExpenseAllocationStatus::class,
    ];

    protected $attributes = [
        'status' => ExpenseAllocationStatus::PENDING,
    ];

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    public function dimensions(): HasMany
    {
        return $this->hasMany(AllocationDimension::class, 'allocation_id');
    }

    /**
     * Get dimensions grouped by type for easier UI handling.
     */
    public function getDimensionsByType(): array
    {
        $dimensionsByType = [];

        foreach ($this->dimensions as $dimension) {
            $type = $dimension->dimension_type->value;

            if (!isset($dimensionsByType[$type])) {
                $dimensionsByType[$type] = [];
            }
            $dimensionsByType[$type][] = $dimension;
        }

        return $dimensionsByType;
    }

    /**
     * Check if allocation has a specific dimension type.
     */
    public function hasDimension(string $dimensionType): bool
    {
        return $this->dimensions()
            ->where('dimension_type', $dimensionType)
            ->exists()
        ;
    }

    /**
     * Get dimension value for a specific type (assumes single value per type).
     */
    public function getDimensionValue(string $dimensionType): ?AllocationDimension
    {
        // @phpstan-ignore-next-line
        return $this->dimensions()
            ->where('dimension_type', $dimensionType)
            ->first()
        ;
    }
}
