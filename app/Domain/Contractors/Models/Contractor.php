<?php

namespace App\Domain\Contractors\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Common\Traits\HaveAddresses;
use App\Domain\Tenant\Concerns\BelongsToTenant;
use Carbon\Carbon;
use Database\Factories\ContractorFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string  $id
 * @property string  $tenant_id
 * @property string  $name
 * @property string  $email
 * @property ?string $phone
 * @property ?string $country
 * @property ?string $tax_id
 * @property ?string $description
 * @property bool    $is_active
 * @property bool    $is_buyer
 * @property bool    $is_supplier
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 * @property ?Carbon $deleted_at
 */
class Contractor extends BaseModel
{
    use SoftDeletes;
    use BelongsToTenant;
    use HaveAddresses;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'phone',
        'country',
        'tax_id',
        'description',
        'is_active',
        'is_buyer',
        'is_supplier',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'is_buyer'    => 'boolean',
        'is_supplier' => 'boolean',
    ];

    protected static function newFactory()
    {
        return ContractorFactory::new();
    }
}
