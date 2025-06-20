<?php

namespace App\Domain\Common\Traits;

use App\Domain\Common\Models\BankAccount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @mixin Model
 */
trait HaveBankAccounts
{
    public function bankAccounts(): MorphMany
    {
        return $this->morphMany(BankAccount::class, 'bankable');
    }

    public function defaultBankAccount(): MorphOne
    {
        return $this->morphOne(BankAccount::class, 'bankable')
            ->where('is_default', true)
            ->limit(1)
        ;
    }
}
