<?php

namespace App\Domain\Financial\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Expense\Contracts\AllocationDimensionInterface;
use App\Domain\Expense\Traits\HasAllocationDimensionInterface;
use App\Domain\Tenant\Traits\IsGlobalOrBelongsToTenant;
use Carbon\Carbon;

/**
 * @property string  $id
 * @property ?string $tenant_id
 * @property string  $code
 * @property string  $name
 * @property ?string $description
 * @property bool    $is_active
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 */
class AllocationTransactionType extends BaseModel implements AllocationDimensionInterface
{
    use IsGlobalOrBelongsToTenant;
    use HasAllocationDimensionInterface;

    protected $table = 'allocation_transaction_types';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    /**
     * Scope to active records only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get display name with code prefix.
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->code} - {$this->name}";
    }
}
