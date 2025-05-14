<?php

namespace App\Domain\Common\Models;

use App\Domain\Common\DTOs\AddressMeta;
use App\Domain\Common\Enums\AddressType;
use Carbon\Carbon;
use Database\Factories\AddressFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class Address.
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
 * @property Model       $addressable
 * @property ?Carbon     $created_at
 * @property ?Carbon     $updated_at
 * @property AddressMeta $meta
 */
class Address extends BaseModel
{
    protected $fillable = [
        'tenant_id',
        'country',
        'postal_code',
        'city',
        'street',
        'building',
        'flat',
        'description',
        'type',
        'is_default',
        'meta',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'type'       => AddressType::class,
        'meta'       => AddressMeta::class,
    ];

    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }

    protected static function newFactory()
    {
        return AddressFactory::new();
    }
}
