<?php

namespace App\Domain\Expense\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Expense\Enums\AllocationDimensionType;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Carbon\Carbon;

/**
 * @property string                  $id
 * @property string                  $tenant_id
 * @property AllocationDimensionType $dimension_type
 * @property bool                    $is_enabled
 * @property int                     $display_order
 * @property Carbon                  $created_at
 * @property Carbon                  $updated_at
 */
class TenantDimensionConfiguration extends BaseModel
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'dimension_type',
        'is_enabled',
        'display_order',
    ];

    protected $casts = [
        'dimension_type' => AllocationDimensionType::class,
        'is_enabled'     => 'boolean',
        'display_order'  => 'integer',
    ];
}
