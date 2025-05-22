<?php

namespace App\Domain\Contractors\Models;

use App\Domain\Common\Models\BankAccount;
use App\Domain\Common\Traits\HasActivityLog;
use App\Domain\Common\Traits\HasActivityLogging;
use App\Domain\Contractors\Enums\ContractorActivityType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string     $id
 * @property string     $tenant_id
 * @property string     $contractor_id
 * @property string     $bank_name
 * @property string     $account_number
 * @property string     $swift
 * @property string     $iban
 * @property bool       $is_default
 * @property Contractor $contractor
 *
 * @description This model should extend the `BankAccount` model.
 */
class ContractorBankAccount extends BankAccount
{
    use HasActivityLog;
    use HasActivityLogging;

    protected $fillable = [
        'contractor_id',
        'bank_name',
        'account_number',
        'swift',
        'iban',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(Contractor::class);
    }

    protected static function booted()
    {
        static::created(function ($bankAccount) {
            $bankAccount->contractor->logModelActivity(ContractorActivityType::BankAccountCreated->value, $bankAccount);
        });

        static::updated(function ($bankAccount) {
            $bankAccount->contractor->logModelActivity(ContractorActivityType::BankAccountUpdated->value, $bankAccount);
        });

        static::deleted(function ($bankAccount) {
            $bankAccount->contractor->logModelActivity(ContractorActivityType::BankAccountDeleted->value, $bankAccount);
        });
    }

    public function setDefault(): void
    {
        $this->contractor->bankAccounts()->update(['is_default' => false]);
        $this->update(['is_default' => true]);

        $this->contractor->logModelActivity(ContractorActivityType::BankAccountSetDefault->value, $this);
    }
}
