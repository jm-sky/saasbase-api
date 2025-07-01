<?php

namespace App\Domain\Contractors\Actions;

use App\Domain\Common\DTOs\BankAccountDTO;
use App\Domain\Common\Models\BankAccount;
use App\Domain\Contractors\Enums\ContractorActivityType;
use App\Domain\Contractors\Models\Contractor;

class CreateContractorBankAccount
{
    public static function execute(Contractor $contractor, BankAccountDTO $data): BankAccount
    {
        $haveBankAccounts = $contractor->bankAccounts()->exists();

        if (!$haveBankAccounts) {
            $data->isDefault = true;
        }

        /** @var BankAccount $bankAccount */
        $bankAccount = $contractor->bankAccounts()->create($data->toDbArray());

        $contractor->logModelActivity(ContractorActivityType::BankAccountCreated->value, $bankAccount);

        return $bankAccount;
    }
}
