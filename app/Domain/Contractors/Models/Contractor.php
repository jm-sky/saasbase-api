<?php

namespace App\Domain\Contractors\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $name
 * @property string $email
 * @property ?string $phone
 * @property ?string $address
 * @property ?string $city
 * @property ?string $state
 * @property ?string $zip_code
 * @property ?string $country
 * @property ?string $tax_id
 * @property ?string $notes
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property ?\Carbon\Carbon $deleted_at
 */
class Contractor extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'tax_id',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
