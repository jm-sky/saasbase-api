<?php

namespace App\Domain\Common\Models;

use App\Domain\Tenant\Traits\IsGlobalOrBelongsToTenant;
use Carbon\Carbon;

/**
 * @property string  $id
 * @property ?string $tenant_id
 * @property string  $code
 * @property string  $name
 * @property ?string $description
 * @property ?string $address
 * @property bool    $is_active
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 */
class AllocationLocation extends BaseModel
{
    use IsGlobalOrBelongsToTenant;

    protected $table = 'allocation_locations';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'description',
        'address',
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

    /**
     * Get full address for display.
     */
    public function getFullAddressAttribute(): string
    {
        return $this->address ? "{$this->name}, {$this->address}" : $this->name;
    }
}
