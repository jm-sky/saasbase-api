<?php

namespace App\Domain\Financial\Models;

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
class AllocationRelatedTransactionCategory extends BaseModel
{
    use IsGlobalOrBelongsToTenant;

    protected $table = 'allocation_related_transaction_categories';

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
}
