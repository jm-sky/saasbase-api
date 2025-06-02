<?php

namespace App\Domain\Subscription\Models;

use App\Domain\Common\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string  $id
 * @property string  $billable_type
 * @property string  $billable_id
 * @property string  $name
 * @property string  $address_line1
 * @property ?string $address_line2
 * @property string  $postal_code
 * @property string  $city
 * @property ?string $state
 * @property string  $country
 * @property ?string $vat_id
 * @property ?string $tax_id
 * @property ?string $email_for_billing
 * @property ?string $note
 * @property ?Model  $billable
 */
class BillingInfo extends BaseModel
{
    protected $fillable = [
        'billable_type',
        'billable_id',
        'name',
        'address_line1',
        'address_line2',
        'postal_code',
        'city',
        'state',
        'country',
        'vat_id',
        'tax_id',
        'email_for_billing',
        'note',
    ];

    public function billable()
    {
        return $this->morphTo();
    }
}
