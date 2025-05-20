<?php

namespace App\Domain\Contractors\Models;

use App\Domain\Common\Models\BankAccount;
use App\Domain\Common\Traits\HasActivityLog;
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
            activity()
                ->performedOn($bankAccount->contractor)
                ->withProperties([
                    'tenant_id'       => request()->user()?->getTenantId(),
                    'bank_account_id' => $bankAccount->id,
                ])
                ->event(ContractorActivityType::BankAccountCreated->value)
                ->log('Contractor bank account created')
            ;
        });

        static::updated(function ($bankAccount) {
            activity()
                ->performedOn($bankAccount->contractor)
                ->withProperties([
                    'tenant_id'       => request()->user()?->getTenantId(),
                    'bank_account_id' => $bankAccount->id,
                ])
                ->event(ContractorActivityType::BankAccountUpdated->value)
                ->log('Contractor bank account updated')
            ;
        });

        static::deleted(function ($bankAccount) {
            activity()
                ->performedOn($bankAccount->contractor)
                ->withProperties([
                    'tenant_id'       => request()->user()?->getTenantId(),
                    'bank_account_id' => $bankAccount->id,
                ])
                ->event(ContractorActivityType::BankAccountDeleted->value)
                ->log('Contractor bank account deleted')
            ;
        });
    }

    public function setDefault(): void
    {
        $this->contractor->bankAccounts()->update(['is_default' => false]);
        $this->update(['is_default' => true]);

        activity()
            ->performedOn($this->contractor)
            ->withProperties([
                'tenant_id'       => request()->user()?->getTenantId(),
                'bank_account_id' => $this->id,
            ])
            ->event(ContractorActivityType::BankAccountSetDefault->value)
            ->log('Contractor bank account set as default')
        ;
    }
}
