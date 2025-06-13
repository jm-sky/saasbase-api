<?php

namespace App\Domain\Exchanges\Models;

use App\Domain\Common\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
