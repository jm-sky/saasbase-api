<?php

namespace App\Domain\ShareToken\Traits;

use App\Domain\ShareToken\Models\ShareToken;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasShareTokens
{
    public function shareTokens(): HasMany
    {
        return $this->hasMany(ShareToken::class, 'shareable_id', 'id');
    }
}
