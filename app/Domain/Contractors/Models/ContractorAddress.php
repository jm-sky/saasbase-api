<?php

namespace App\Domain\Contractors\Models;

use App\Domain\Common\Enums\AddressType;
use App\Domain\Common\Models\Address;
use App\Domain\Tenant\Concerns\BelongsToTenant;
use Carbon\Carbon;
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
class ContractorAddress extends Address
{
    use BelongsToTenant;

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
}
