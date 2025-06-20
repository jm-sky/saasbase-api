<?php

namespace App\Domain\Exchanges\Models;

use App\Domain\Common\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string         $id
 * @property string         $name
 * @property string         $currency
 * @property ExchangeRate[] $rates
 * @property Carbon         $created_at
 * @property Carbon         $updated_at
 */
class Exchange extends BaseModel
{
    protected $fillable = [
        'name',
        'currency',
    ];

    public function rates(): HasMany
    {
        return $this->hasMany(ExchangeRate::class);
    }
}
