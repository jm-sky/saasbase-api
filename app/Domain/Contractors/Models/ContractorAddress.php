<?php

namespace App\Domain\Contractors\Models;

use App\Domain\Common\Enums\AddressType;
use App\Domain\Common\Traits\HasActivityLog;
use App\Domain\Contractors\Enums\ContractorActivityType;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class ContractorAddress.
 *
 * @property string      $id
 * @property ?string     $tenant_id
 * @property string      $country
 * @property ?string     $postal_code
 * @property string      $city
 * @property ?string     $street
 * @property ?string     $building
 * @property ?string     $flat
 * @property ?string     $description
 * @property AddressType $type
 * @property bool        $is_default
 * @property string      $addressable_id
 * @property string      $addressable_type
 * @property Contractor  $addressable
 * @property ?Carbon     $created_at
 * @property ?Carbon     $updated_at
 */
class ContractorAddress extends Model
{
    use BelongsToTenant;
    use HasActivityLog;

    /**
     * The table associated with the model.
     * Explicitly set to use the parent's table.
     *
     * @var string
     */
    protected $table = 'addresses';

    protected $fillable = [
        'contractor_id',
        'street',
        'city',
        'state',
        'postal_code',
        'country',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Get the parent addressable model.
     * This overrides the parent method to ensure type safety for contractors.
     */
    public function addressable(): MorphTo
    {
        return $this->morphTo()->where('addressable_type', Contractor::class);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $address) {
            $address->addressable_type = Contractor::class;
        });
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(Contractor::class);
    }

    protected static function booted()
    {
        static::created(function ($address) {
            activity()
                ->performedOn($address->contractor)
                ->withProperties([
                    'tenant_id'  => request()->user()?->tenant_id,
                    'address_id' => $address->id,
                ])
                ->event(ContractorActivityType::ADDRESS_CREATED->value)
                ->log('Contractor address created')
            ;
        });

        static::updated(function ($address) {
            activity()
                ->performedOn($address->contractor)
                ->withProperties([
                    'tenant_id'  => request()->user()?->tenant_id,
                    'address_id' => $address->id,
                ])
                ->event(ContractorActivityType::ADDRESS_UPDATED->value)
                ->log('Contractor address updated')
            ;
        });

        static::deleted(function ($address) {
            activity()
                ->performedOn($address->contractor)
                ->withProperties([
                    'tenant_id'  => request()->user()?->tenant_id,
                    'address_id' => $address->id,
                ])
                ->event(ContractorActivityType::ADDRESS_DELETED->value)
                ->log('Contractor address deleted')
            ;
        });
    }
}
