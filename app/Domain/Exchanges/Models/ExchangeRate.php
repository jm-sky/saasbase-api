<?php

namespace App\Domain\Exchanges\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Exchanges\Enums\ExchangeRateSource;
use Carbon\Carbon;
use Database\Factories\Domain\Exchanges\ExchangeRateFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string   $id
 * @property string   $base_currency ISO-4217 code
 * @property string   $currency      ISO-4217 code
 * @property Carbon   $date
 * @property float    $rate
 * @property string   $table         Table name of the rate i.e. A
 * @property string   $source        Provider of the rate i.e. NPB
 * @property ?string  $no            Internal publication number, e.g. 001/A/NBP/2025
 * @property Carbon   $created_at
 * @property Currency $baseCurrency
 * @property Currency $quoteCurrency
 */
class ExchangeRate extends BaseModel
{
    public $timestamps = false;

    protected $fillable = [
        'id',
        'base_currency',
        'currency',
        'date',
        'rate',
        'table',
        'source',
        'no',
        'created_at',
    ];

    protected $casts = [
        'date'       => 'date',
        'rate'       => 'float',
        'source'     => ExchangeRateSource::class,
        'created_at' => 'datetime',
    ];

    public function baseCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'base_currency', 'code');
    }

    public function quoteCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency', 'code');
    }

    protected static function newFactory()
    {
        return ExchangeRateFactory::new();
    }
}
