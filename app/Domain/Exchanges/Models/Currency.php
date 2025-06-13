<?php

namespace App\Domain\Exchanges\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property string                    $code
 * @property string                    $name
 * @property string                    $symbol
 * @property Collection|ExchangeRate[] $baseExchangeRates
 * @property Collection|ExchangeRate[] $quoteExchangeRates
 */
class Currency extends Model
{
    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = 'code';

    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'name',
        'symbol',
    ];

    /**
     * Exchange rates where this currency is the base currency.
     */
    public function baseExchangeRates(): HasMany
    {
        return $this->hasMany(ExchangeRate::class, 'base_currency', 'code');
    }

    /**
     * Exchange rates where this currency is the quote currency.
     */
    public function quoteExchangeRates(): HasMany
    {
        return $this->hasMany(ExchangeRate::class, 'currency', 'code');
    }
}
