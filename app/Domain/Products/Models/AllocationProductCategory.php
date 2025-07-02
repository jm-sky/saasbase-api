<?php

namespace App\Domain\Products\Models;

use App\Domain\Common\Models\BaseModel;
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
class AllocationProductCategory extends BaseModel
{
    use IsGlobalOrBelongsToTenant;

    protected $table = 'allocation_product_categories';

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
