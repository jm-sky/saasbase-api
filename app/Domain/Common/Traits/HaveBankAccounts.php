<?php

namespace App\Domain\Common\Traits;

use App\Domain\Common\Models\BankAccount;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HaveBankAccounts
{
    public function bankAccounts(): MorphMany
    {
        return $this->morphMany(BankAccount::class, 'bankable');
    }
}
