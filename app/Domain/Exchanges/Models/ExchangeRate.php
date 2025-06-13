<?php

namespace App\Domain\Exchanges\Models;

use App\Domain\Common\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string   $id
 * @property string   $base_currency
 * @property string   $currency
 * @property Carbon   $date
 * @property float    $rate
 * @property string   $table
 * @property string   $source
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
        'created_at',
    ];

    protected $casts = [
        'date'       => 'date',
        'rate'       => 'float',
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
}
