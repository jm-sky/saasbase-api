<?php

namespace App\Domain\Tenant\Models;

use App\Domain\Common\Enums\AddressType;
use App\Domain\Common\Models\Address;
use App\Domain\Tenant\Concerns\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TenantAddress.
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
 */
class TenantAddress extends Address
{
    use BelongsToTenant;
}
