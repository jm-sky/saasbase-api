<?php

namespace App\Domain\Contractors\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Common\Traits\HasActivityLog;
use App\Domain\Contractors\Enums\ContractorActivityType;
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
    use HasActivityLog;

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
            activity()
                ->performedOn($contact->contractor)
                ->withProperties([
                    'tenant_id'  => request()->user()->tenant_id,
                    'contact_id' => $contact->id,
                ])
                ->event(ContractorActivityType::ContactCreated->value)
                ->log('Contractor contact created')
            ;
        });

        static::updated(function ($contact) {
            activity()
                ->performedOn($contact->contractor)
                ->withProperties([
                    'tenant_id'  => request()->user()->tenant_id,
                    'contact_id' => $contact->id,
                ])
                ->event(ContractorActivityType::ContactUpdated->value)
                ->log('Contractor contact updated')
            ;
        });

        static::deleted(function ($contact) {
            activity()
                ->performedOn($contact->contractor)
                ->withProperties([
                    'tenant_id'  => request()->user()->tenant_id,
                    'contact_id' => $contact->id,
                ])
                ->event(ContractorActivityType::ContactDeleted->value)
                ->log('Contractor contact deleted')
            ;
        });
    }
}
