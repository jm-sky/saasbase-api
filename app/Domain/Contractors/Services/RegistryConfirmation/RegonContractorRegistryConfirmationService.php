<?php

namespace App\Domain\Contractors\Services\RegistryConfirmation;

use App\Domain\Common\DTOs\AddressDTO;
use App\Domain\Common\DTOs\CommonCompanyLookupData;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Contractors\Services\RegistryConfirmation\Contracts\RegistryConfirmationServiceInterface;
use App\Domain\Utils\Enums\RegistryConfirmationType;
use App\Domain\Utils\Models\RegistryConfirmation;
use App\Services\RegonLookup\DTOs\RegonReportUnified;

class RegonContractorRegistryConfirmationService implements RegistryConfirmationServiceInterface
{
    public function __construct(
        private readonly DataComparatorService $comparatorService,
    ) {
    }

    /**
     * Confirm contractor data against REGON registry data.
     */
    public function confirmContractorData(Contractor $contractor, $registryData): array
    {
        $confirmations = [];

        // Convert REGON data to common format
        $commonData = $this->convertToCommonData($registryData);

        if (!$commonData) {
            return $confirmations;
        }

        // Confirm company data
        $companyConfirmation = $this->confirmCompanyData($contractor, $commonData, $registryData);

        if ($companyConfirmation) {
            $confirmations[] = $companyConfirmation;
        }

        // Confirm address data if available
        if ($contractor->defaultAddress && $commonData->address) {
            $addressConfirmation = $this->confirmAddressData($contractor, $commonData, $registryData);

            if ($addressConfirmation) {
                $confirmations[] = $addressConfirmation;
            }
        }

        return $confirmations;
    }

    /**
     * Get the registry type this service handles.
     */
    public function getRegistryType(): string
    {
        return RegistryConfirmationType::Regon->value;
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
                'type'             => RegistryConfirmationType::Regon->value,
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
     * Confirm address data.
     */
    private function confirmAddressData(Contractor $contractor, CommonCompanyLookupData $commonData, $registryData): ?RegistryConfirmation
    {
        $contractorAddress = $contractor->defaultAddress;
        $registryAddress   = $commonData->address;

        // Check if we have required data
        if (!$contractorAddress->street || !$contractorAddress->city
            || !$contractorAddress->postalCode || !$contractorAddress->country) {
            return null;
        }

        if (!$registryAddress->street || !$registryAddress->city
            || !$registryAddress->postalCode || !$registryAddress->country) {
            return null;
        }

        // Prepare payload for comparison
        $payload = [
            'street'     => $contractorAddress->street,
            'city'       => $contractorAddress->city,
            'postalCode' => $contractorAddress->postalCode,
            'country'    => $contractorAddress->country,
        ];

        // Convert Address model to AddressDTO for comparison
        $contractorAddressDTO = $this->convertAddressToDTO($contractorAddress);

        // Compare addresses
        $addressMatch = $this->comparatorService->compareAddresses($contractorAddressDTO, $registryAddress);

        // Prepare result data
        $result = [
            'registryData' => [
                'street'     => $registryAddress->street,
                'city'       => $registryAddress->city,
                'postalCode' => $registryAddress->postalCode,
                'country'    => $registryAddress->country,
            ],
            'comparison' => [
                'addressMatch' => $addressMatch,
            ],
            'rawData' => $this->extractRawData($registryData),
        ];

        return $contractor->registryConfirmations()->updateOrCreate(
            [
                'type'             => RegistryConfirmationType::Address->value,
                'confirmable_id'   => $contractor->id,
                'confirmable_type' => get_class($contractor),
            ],
            [
                'payload'    => $payload,
                'result'     => $result,
                'success'    => $addressMatch,
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

        // Handle different REGON data types
        if ($registryData instanceof RegonReportUnified) {
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

    /**
     * Convert Address model to AddressDTO.
     */
    private function convertAddressToDTO($address): AddressDTO
    {
        return new AddressDTO(
            country: $address->country,
            city: $address->city,
            street: $address->street,
            building: $address->building,
            flat: $address->flat,
            postalCode: $address->postal_code,
            type: $address->type,
            isDefault: $address->is_default,
        );
    }
}
