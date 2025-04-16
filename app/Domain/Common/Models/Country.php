<?php

namespace App\Domain\Common\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends BaseModel
{
    use SoftDeletes;

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
