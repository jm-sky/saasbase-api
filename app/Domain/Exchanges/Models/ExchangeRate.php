<?php

namespace App\Domain\Exchanges\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $exchange_id
 * @property Carbon $date
 * @property float  $rate
 * @property Carbon $created_at
 */
class ExchangeRate extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'exchange_id',
        'date',
        'rate',
    ];

    protected $casts = [
        'date' => 'date',
        'rate' => 'float',
    ];

    public function exchange(): BelongsTo
    {
        return $this->belongsTo(Exchange::class);
    }
}
