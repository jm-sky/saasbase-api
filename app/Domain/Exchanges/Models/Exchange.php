<?php

namespace App\Domain\Exchanges\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exchange extends Model
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
