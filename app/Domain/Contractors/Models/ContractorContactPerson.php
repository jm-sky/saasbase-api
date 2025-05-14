<?php

namespace App\Domain\Contractors\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Tenant\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string     $id
 * @property string     $tenant_id
 * @property string     $contractor_id
 * @property string     $name
 * @property ?string    $email
 * @property ?string    $phone
 * @property ?string    $position
 * @property ?string    $description
 * @property string     $created_at
 * @property string     $updated_at
 * @property Contractor $contractor
 * @property Tenant     $tenant
 */
class ContractorContactPerson extends BaseModel
{
    use BelongsToTenant;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'position',
        'description',
        'contractor_id',
    ];

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(Contractor::class);
    }
}
