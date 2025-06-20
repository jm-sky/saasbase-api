<?php

namespace App\Domain\Contractors\Actions;

use App\Domain\Common\DTOs\AddressDTO;
use App\Domain\Common\Models\Address;
use App\Domain\Contractors\Enums\ContractorActivityType;
use App\Domain\Contractors\Models\Contractor;

class CreateContractorAddress
{
    public static function execute(Contractor $contractor, AddressDTO $data): Address
    {
        $haveAddresses = $contractor->addresses()->exists();

        if (!$haveAddresses) {
            $data->isDefault = true;
        }

        $address = $contractor->addresses()->create($data->toDbArray());

        $contractor->logModelActivity(ContractorActivityType::AddressCreated->value, $address);

        return $address;
    }
}
