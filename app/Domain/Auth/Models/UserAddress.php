<?php

namespace App\Domain\Auth\Models;

use App\Domain\Common\Enums\AddressType;
use App\Domain\Common\Models\Address;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class UserAddress.
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
 * @property User        $addressable
 * @property ?Carbon     $created_at
 * @property ?Carbon     $updated_at
 */
class UserAddress extends Address
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'addresses';

    /**
     * Get the parent addressable model.
     * This overrides the parent method to ensure type safety for users.
     */
    public function addressable(): MorphTo
    {
        return $this->morphTo()->where('addressable_type', User::class);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $address) {
            $address->addressable_type = User::class;
        });
    }
}
