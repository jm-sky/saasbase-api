<?php

namespace App\Domain\Common\Models;

class Country extends BaseModel
{
    protected $fillable = [
        'name',
        'code',
        'code3',
        'numeric_code',
        'phone_code',
        'capital',
        'currency',
        'currency_code',
        'currency_symbol',
        'tld',
        'native',
        'region',
        'subregion',
        'emoji',
        'emojiU',
    ];
}
