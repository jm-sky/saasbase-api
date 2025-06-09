<?php

namespace App\Domain\IbanInfo\Models;

use App\Domain\Common\Models\BaseModel;

class BankCode extends BaseModel
{
    protected $fillable = [
        'country_code',
        'bank_code',
        'bank_name',
        'swift',
        'currency',
        'validated_at',
    ];

    protected $casts = [
        'validated_at' => 'datetime',
    ];
}
