<?php

namespace App\Domain\Contractors\Services;

use App\Domain\Contractors\Models\Contractor;
use App\Domain\Utils\DTOs\AllLookupResults;
use App\Domain\Utils\DTOs\CompanyContext;
use App\Domain\Utils\Enums\RegistryConfirmationType;
use App\Domain\Utils\Services\CompanyDataFetcherService;

/**
 * Flow:
 * - Should fetch regon, mf & vies data for contractor.
 * - Should confirm header company data (name, nip, regon)
 *   - Create Regon Confirmation
 *   - Create Vies Confirmation
 *   - Create Mf Confirmation
 * - Should confirm address
 *   - Create Regon Confirmation
 *   - Create Vies Confirmation
 *   - Create Mf Confirmation
 * - Should confirm bank account
 *   - Create Mf Confirmation
 */
class ContractorRegistryConfirmationService
{
    public CompanyContext $companyContext;

    public AllLookupResults $allLookupResults;

    public function __construct(
        private readonly CompanyDataFetcherService $dataFetcherService,
    ) {
    }

    public function confirm(Contractor $contractor)
    {
        $this->companyContext = new CompanyContext(
            $contractor->vat_id,
            $contractor->regon,
            $contractor->country,
            force: false,
        );

        $this->allLookupResults = $this->dataFetcherService->fetch($this->companyContext);

        $this->confirmCompanyData($contractor);
        $this->confirmAddress($contractor);
        $this->confirmBankAccount($contractor);
    }

    protected function confirmCompanyData(Contractor $contractor)
    {
        $payload = [
            'name'  => $contractor->name,
            'nip'   => $contractor->vat_id,
            'regon' => $contractor->regon,
        ];

        return $contractor
            ->registryConfirmations()
            ->firstOrCr([
                'type' => RegistryConfirmationType::Regon->value,
            ], [
                'payload'    => $payload,
                'result'     => $this->allLookupResults->regon,
                'success'    => true,
                'checked_at' => now(),
            ])
        ;
    }

    protected function confirmAddress(Contractor $contractor)
    {
        if (!$contractor->defaultAddress) {
            return;
        }

        $payload = [
            'country' => $contractor->defaultAddress->country,
            'city'    => $contractor->defaultAddress->city,
            'street'  => $contractor->defaultAddress->street,
        ];

        return $contractor
            ->registryConfirmations()
            ->firstOrCreate([
                'type' => RegistryConfirmationType::Address->value,
            ], [
                'payload'    => $payload,
                'result'     => $this->allLookupResults->regon,
                'success'    => true,
                'checked_at' => now(),
            ])
        ;
    }

    protected function confirmBankAccount(Contractor $contractor)
    {
        if (!$contractor->defaultBankAccount) {
            return;
        }

        $payload = [
            'iban' => $contractor->defaultBankAccount->iban,
        ];

        return $contractor
            ->registryConfirmations()
            ->firstOrCreate([
                'type' => RegistryConfirmationType::Mf->value,
            ], [
                'payload'    => $payload,
                'result'     => $this->allLookupResults->mf,
                'success'    => true,
                'checked_at' => now(),
            ])
        ;
    }
}
