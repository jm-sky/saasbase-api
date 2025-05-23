<?php

namespace App\Domain\Contractors\Models;

use App\Domain\Common\Enums\AddressType;
use App\Domain\Common\Models\Address;
use App\Domain\Common\Traits\HasActivityLog;
use App\Domain\Common\Traits\HasActivityLogging;
use App\Domain\Contractors\Enums\ContractorActivityType;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class ContractorAddress.
 *
 * @property string      $id
 * @property string      $tenant_id
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
 *
 * @description This model should extend the `Address` model.
 */
class ContractorAddress extends Address
{
    use BelongsToTenant;
    use HasActivityLog;
    use HasActivityLogging;

    /**
     * The table associated with the model.
     * Explicitly set to use the parent's table.
     *
     * @var string
     */
    protected $table = 'addresses';

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
            $address->contractor->logModelActivity(ContractorActivityType::AddressCreated->value, $address);
        });

        static::updated(function ($address) {
            $address->contractor->logModelActivity(ContractorActivityType::AddressUpdated->value, $address);
        });

        static::deleted(function ($address) {
            $address->contractor->logModelActivity(ContractorActivityType::AddressDeleted->value, $address);
        });
    }
}
