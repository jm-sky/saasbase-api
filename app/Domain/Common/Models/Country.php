<?php

namespace App\Domain\Common\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Represents a country in the system.
 *
 * @property string  $code            Two-letter country code (ISO 3166-1 alpha-2)
 * @property string  $name            Full name of the country
 * @property string  $code3           Three-letter country code (ISO 3166-1 alpha-3)
 * @property string  $numeric_code    Three-digit country code (ISO 3166-1 numeric)
 * @property string  $phone_code      International calling code
 * @property string  $capital         Capital city name
 * @property string  $currency        Currency name
 * @property string  $currency_code   Three-letter currency code (ISO 4217)
 * @property string  $currency_symbol Currency symbol
 * @property string  $tld             Top-level domain
 * @property string  $native          Native name of the country
 * @property string  $region          Geographical region
 * @property string  $subregion       Geographical subregion
 * @property string  $emoji           Country flag emoji
 * @property string  $emojiU          Unicode representation of the flag emoji
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 * @property ?Carbon $deleted_at
 */
class Country extends Model
{
    protected $primaryKey = 'code';

    protected $keyType = 'string';

    public $incrementing = false;

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
