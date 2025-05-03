<?php

namespace App\Domain\Common\Concerns;

use App\Domain\Common\Models\Address;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HaveAddresses
{
    public function addresses(): MorphMany
    {
        return $this->morphMany(Address::class, 'addressable');
    }
}
