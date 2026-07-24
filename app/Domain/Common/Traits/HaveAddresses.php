<?php

namespace App\Domain\Common\Traits;

use App\Domain\Common\Models\Address;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @mixin Model
 */
trait HaveAddresses
{
    /**
     * @return MorphMany<Address, $this>
     */
    public function addresses(): MorphMany
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    /**
     * @return MorphOne<Address, $this>
     */
    public function defaultAddress(): MorphOne
    {
        return $this->morphOne(Address::class, 'addressable')
            ->where('is_default', true)
            ->limit(1);
    }
}
