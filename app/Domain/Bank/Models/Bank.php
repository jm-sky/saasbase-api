<?php

namespace App\Domain\Bank\Models;

use App\Domain\Common\Models\BaseModel;
use Carbon\Carbon;

/**
 * @property string  $id           UUID
 * @property string  $country      Country code (e.g. "PL")
 * @property string  $bank_name    Full name of the bank
 * @property string  $branch_name  Full name of the branch
 * @property string  $bank_code    First 4 digits of routing code
 * @property string  $routing_code 8-digit routing code (numer rozliczeniowy)
 * @property ?string $swift        SWIFT/BIC code
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 */
class Bank extends BaseModel
{
    protected $fillable = [
        'country',
        'bank_name',
        'branch_name',
        'bank_code',
        'routing_code',
        'swift',
    ];

    protected $casts = [
        'swift' => 'string',
    ];
}
