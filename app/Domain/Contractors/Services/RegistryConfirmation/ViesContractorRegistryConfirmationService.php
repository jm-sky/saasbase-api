<?php

namespace App\Domain\Contractors\Services\RegistryConfirmation;

use App\Domain\Common\DTOs\CommonCompanyLookupData;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Contractors\Services\RegistryConfirmation\Contracts\RegistryConfirmationServiceInterface;
use App\Domain\Utils\Enums\RegistryConfirmationType;
use App\Domain\Utils\Models\RegistryConfirmation;
use App\Services\ViesLookup\DTOs\ViesLookupResultDTO;

class ViesContractorRegistryConfirmationService implements RegistryConfirmationServiceInterface
{
    public function __construct(
        private readonly DataComparatorService $comparatorService,
    ) {
    }

    /**
     * Confirm contractor data against VIES registry data.
     */
    public function confirmContractorData(Contractor $contractor, $registryData): array
    {
        $confirmations = [];

        // Convert VIES data to common format
        $commonData = $this->convertToCommonData($registryData);

        if (!$commonData) {
            return $confirmations;
        }

        // Confirm company data
        $companyConfirmation = $this->confirmCompanyData($contractor, $commonData, $registryData);

        if ($companyConfirmation) {
            $confirmations[] = $companyConfirmation;
        }

        // Note: VIES doesn't provide reliable address data, so we don't confirm addresses
        // Note: VIES doesn't provide bank account data

        return $confirmations;
    }

    /**
     * Get the registry type this service handles.
     */
    public function getRegistryType(): string
    {
        return RegistryConfirmationType::Vies->value;
    }

    /**
     * Confirm company data (name, VAT ID).
     */
    private function confirmCompanyData(Contractor $contractor, CommonCompanyLookupData $commonData, $registryData): ?RegistryConfirmation
    {
        // Check if we have required data
        if (!$contractor->name || !$contractor->vat_id || !$commonData->vatId) {
            return null;
        }

        // VIES validation requires the VAT number to be valid
        if (!$this->isViesValidationSuccessful($registryData)) {
            return null;
        }

        // Prepare payload for comparison
        $payload = [
            'name'    => $contractor->name,
            'vatId'   => $contractor->vat_id,
            'country' => $contractor->country,
        ];

        // Compare data
        $vatIdMatch = $this->comparatorService->compareVatIds($contractor->vat_id, $commonData->vatId);
        $nameMatch  = false;

        // Only compare names if VIES provides a name
        if ($commonData->name) {
            $nameMatch = $this->comparatorService->compareNames($contractor->name, $commonData->name);
        }

        // Company data is confirmed if VAT ID is valid and matches
        // Name comparison is optional since VIES doesn't always provide names
        $isConfirmed = $vatIdMatch && ($nameMatch || !$commonData->name);

        // Prepare result data
        $result = [
            'registryData' => [
                'name'    => $commonData->name,
                'vatId'   => $commonData->vatId,
                'country' => $commonData->country,
                'valid'   => $this->isViesValidationSuccessful($registryData),
            ],
            'comparison' => [
                'vatIdMatch'   => $vatIdMatch,
                'nameMatch'    => $nameMatch,
                'nameProvided' => !empty($commonData->name),
            ],
            'rawData' => $this->extractRawData($registryData),
        ];

        return $contractor->registryConfirmations()->updateOrCreate(
            [
                'type'             => RegistryConfirmationType::Vies->value,
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

        // Handle VIES-specific data types
        if ($registryData instanceof ViesLookupResultDTO) {
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
     * Check if VIES validation was successful.
     */
    private function isViesValidationSuccessful($registryData): bool
    {
        if ($registryData instanceof ViesLookupResultDTO) {
            return $registryData->valid;
        }

        if (is_array($registryData) && isset($registryData['valid'])) {
            return $registryData['valid'];
        }

        if (is_object($registryData) && property_exists($registryData, 'valid')) {
            return $registryData->valid;
        }

        return false;
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
