<?php

namespace App\Domain\Contractors\Services\RegistryConfirmation;

use App\Domain\Common\DTOs\BankAccountDTO;
use App\Domain\Common\DTOs\CommonCompanyLookupData;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Contractors\Services\RegistryConfirmation\Contracts\RegistryConfirmationServiceInterface;
use App\Domain\Utils\Enums\RegistryConfirmationType;
use App\Domain\Utils\Models\RegistryConfirmation;
use App\Services\MfLookup\DTOs\MfLookupResultDTO;

class MfContractorRegistryConfirmationService implements RegistryConfirmationServiceInterface
{
    public function __construct(
        private readonly DataComparatorService $comparatorService,
    ) {
    }

    /**
     * Confirm contractor data against MF registry data.
     */
    public function confirmContractorData(Contractor $contractor, $registryData): array
    {
        $confirmations = [];

        // Convert MF data to common format
        $commonData = $this->convertToCommonData($registryData);

        if (!$commonData) {
            return $confirmations;
        }

        // Confirm company data
        $companyConfirmation = $this->confirmCompanyData($contractor, $commonData, $registryData);

        if ($companyConfirmation) {
            $confirmations[] = $companyConfirmation;
        }

        // Confirm bank account data if available
        if ($contractor->defaultBankAccount && $commonData->bankAccount) {
            $bankAccountConfirmation = $this->confirmBankAccountData($contractor, $commonData, $registryData);

            if ($bankAccountConfirmation) {
                $confirmations[] = $bankAccountConfirmation;
            }
        }

        return $confirmations;
    }

    /**
     * Get the registry type this service handles.
     */
    public function getRegistryType(): string
    {
        return RegistryConfirmationType::Mf->value;
    }

    /**
     * Confirm company data (name, VAT ID, REGON).
     */
    private function confirmCompanyData(Contractor $contractor, CommonCompanyLookupData $commonData, $registryData): ?RegistryConfirmation
    {
        // Check if we have required data
        if (!$contractor->name || !$contractor->vat_id || !$commonData->name || !$commonData->vatId) {
            return null;
        }

        // Prepare payload for comparison
        $payload = [
            'name'  => $contractor->name,
            'vatId' => $contractor->vat_id,
            'regon' => $contractor->regon,
        ];

        // Compare data
        $nameMatch  = $this->comparatorService->compareNames($contractor->name, $commonData->name);
        $vatIdMatch = $this->comparatorService->compareVatIds($contractor->vat_id, $commonData->vatId);
        $regonMatch = $this->comparatorService->compareRegons($contractor->regon, $commonData->regon);

        // Company data is confirmed if name and VAT ID match
        // REGON is optional but if both have it, it should match
        $isConfirmed = $nameMatch && $vatIdMatch && ($regonMatch || !$contractor->regon || !$commonData->regon);

        // Prepare result data
        $result = [
            'registryData' => [
                'name'  => $commonData->name,
                'vatId' => $commonData->vatId,
                'regon' => $commonData->regon,
            ],
            'comparison' => [
                'nameMatch'  => $nameMatch,
                'vatIdMatch' => $vatIdMatch,
                'regonMatch' => $regonMatch,
            ],
            'rawData' => $this->extractRawData($registryData),
        ];

        return $contractor->registryConfirmations()->updateOrCreate(
            [
                'type'             => RegistryConfirmationType::Mf->value,
                'confirmable_id'   => $contractor->id,
                'confirmable_type' => get_class($contractor),
            ],
            [
                'payload'    => $payload,
                'result'     => $result,
                'success'    => $isConfirmed,
                'checked_at' => now(),
            ]
        );
    }

    /**
     * Confirm bank account data (IBAN validation against VAT ID).
     */
    private function confirmBankAccountData(Contractor $contractor, CommonCompanyLookupData $commonData, $registryData): ?RegistryConfirmation
    {
        $contractorBankAccount = $contractor->defaultBankAccount;
        $registryBankAccount   = $commonData->bankAccount;

        // Check if we have required data
        if (!$contractorBankAccount->iban || !$registryBankAccount->iban) {
            return null;
        }

        // Prepare payload for comparison
        $payload = [
            'iban'  => $contractorBankAccount->iban,
            'vatId' => $contractor->vat_id,
        ];

        // Convert bank account model to DTO for comparison
        $contractorBankAccountDTO = $this->convertBankAccountToDTO($contractorBankAccount);

        // Compare bank accounts
        $ibanMatch = $this->comparatorService->compareBankAccounts($contractorBankAccountDTO, $registryBankAccount);

        // Bank account is confirmed if IBAN matches the one registered for the VAT ID
        $isConfirmed = $ibanMatch;

        // Prepare result data
        $result = [
            'registryData' => [
                'iban'     => $registryBankAccount->iban,
                'bankName' => $registryBankAccount->bankName,
                'swift'    => $registryBankAccount->swift,
                'currency' => $registryBankAccount->currency,
            ],
            'comparison' => [
                'ibanMatch' => $ibanMatch,
            ],
            'rawData' => $this->extractRawData($registryData),
        ];

        return $contractor->registryConfirmations()->updateOrCreate(
            [
                'type'             => RegistryConfirmationType::BankAccount->value,
                'confirmable_id'   => $contractor->id,
                'confirmable_type' => get_class($contractor),
            ],
            [
                'payload'    => $payload,
                'result'     => $result,
                'success'    => $isConfirmed,
                'checked_at' => now(),
            ]
        );
    }

    /**
     * Convert registry data to common format.
     */
    private function convertToCommonData($registryData): ?CommonCompanyLookupData
    {
        if (!$registryData) {
            return null;
        }

        // Handle MF-specific data types
        if ($registryData instanceof MfLookupResultDTO) {
            return $registryData->toCommonLookupData();
        }

        if (is_object($registryData) && method_exists($registryData, 'toCommonLookupData')) {
            return $registryData->toCommonLookupData();
        }

        // Handle array data
        if (is_array($registryData)) {
            return CommonCompanyLookupData::fromArray($registryData);
        }

        return null;
    }

    /**
     * Convert BankAccount model to BankAccountDTO.
     */
    private function convertBankAccountToDTO($bankAccount): BankAccountDTO
    {
        return new BankAccountDTO(
            iban: $bankAccount->iban,
            bankName: $bankAccount->bank_name,
            swift: $bankAccount->swift,
            currency: $bankAccount->currency,
            isDefault: $bankAccount->is_default,
        );
    }

    /**
     * Extract raw data for debugging/logging purposes.
     */
    private function extractRawData($registryData): array
    {
        if (is_object($registryData) && method_exists($registryData, 'toArray')) {
            return $registryData->toArray();
        }

        if (is_array($registryData)) {
            return $registryData;
        }

        return [];
    }
}
