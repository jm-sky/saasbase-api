<?php

namespace App\Domain\Contractors\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Common\Traits\HasActivityLog;
use App\Domain\Common\Traits\HasActivityLogging;
use App\Domain\Contractors\Enums\ContractorActivityType;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Traits\BelongsToTenant;
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
    use HasActivityLog;
    use HasActivityLogging;

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

    protected static function booted()
    {
        static::created(function ($contact) {
            $contact->contractor->logModelActivity(ContractorActivityType::ContactCreated->value, $contact);
        });

        static::updated(function ($contact) {
            $contact->contractor->logModelActivity(ContractorActivityType::ContactUpdated->value, $contact);
        });

        static::deleted(function ($contact) {
            $contact->contractor->logModelActivity(ContractorActivityType::ContactDeleted->value, $contact);
        });
    }
}
