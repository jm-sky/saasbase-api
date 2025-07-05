<?php

namespace App\Domain\Contractors\Services\RegistryConfirmation;

use App\Domain\Common\DTOs\AddressDTO;
use App\Domain\Common\DTOs\BankAccountDTO;
use Illuminate\Support\Facades\Log;

class DataComparatorService
{
    /**
     * Maximum Levenshtein distance for names to be considered a match.
     */
    private const MAX_NAME_DISTANCE = 3;

    /**
     * Compare company names using fuzzy matching.
     */
    public function compareNames(string $contractorName, string $registryName): bool
    {
        try {
            $normalizedContractor = $this->normalizeString($contractorName);
            $normalizedRegistry   = $this->normalizeString($registryName);

            // Exact match after normalization
            if ($normalizedContractor === $normalizedRegistry) {
                return true;
            }

            // Levenshtein distance check
            $distance = levenshtein($normalizedContractor, $normalizedRegistry);

            return $distance <= self::MAX_NAME_DISTANCE;
        } catch (\Exception $e) {
            Log::warning('Error comparing names', [
                'contractor_name' => $contractorName,
                'registry_name'   => $registryName,
                'error'           => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Compare VAT IDs (should be exact match).
     */
    public function compareVatIds(?string $contractorVatId, ?string $registryVatId): bool
    {
        if (!$contractorVatId || !$registryVatId) {
            return false;
        }

        return $this->normalizeVatId($contractorVatId) === $this->normalizeVatId($registryVatId);
    }

    /**
     * Compare REGON numbers (should be exact match).
     */
    public function compareRegons(?string $contractorRegon, ?string $registryRegon): bool
    {
        if (!$contractorRegon || !$registryRegon) {
            return false;
        }

        return $contractorRegon === $registryRegon;
    }

    /**
     * Compare addresses with required fields.
     */
    public function compareAddresses(AddressDTO $contractorAddress, AddressDTO $registryAddress): bool
    {
        // Check required fields
        if (empty($contractorAddress->street) || empty($contractorAddress->city)
            || empty($contractorAddress->postalCode) || empty($contractorAddress->country)) {
            return false;
        }

        if (empty($registryAddress->street) || empty($registryAddress->city)
            || empty($registryAddress->postalCode) || empty($registryAddress->country)) {
            return false;
        }

        // Compare each component
        $streetMatch     = $this->compareStreetAddresses($contractorAddress->street, $registryAddress->street);
        $cityMatch       = $this->compareCities($contractorAddress->city, $registryAddress->city);
        $postalCodeMatch = $this->comparePostalCodes($contractorAddress->postalCode, $registryAddress->postalCode);
        $countryMatch    = strtoupper($contractorAddress->country) === strtoupper($registryAddress->country);

        return $streetMatch && $cityMatch && $postalCodeMatch && $countryMatch;
    }

    /**
     * Compare bank accounts (IBAN should be exact match).
     */
    public function compareBankAccounts(BankAccountDTO $contractorAccount, BankAccountDTO $registryAccount): bool
    {
        if (empty($contractorAccount->iban) || empty($registryAccount->iban)) {
            return false;
        }

        return $this->normalizeIban($contractorAccount->iban) === $this->normalizeIban($registryAccount->iban);
    }

    /**
     * Compare street addresses with fuzzy matching.
     */
    private function compareStreetAddresses(string $contractorStreet, string $registryStreet): bool
    {
        $normalizedContractor = $this->normalizeString($contractorStreet);
        $normalizedRegistry   = $this->normalizeString($registryStreet);

        // Exact match after normalization
        if ($normalizedContractor === $normalizedRegistry) {
            return true;
        }

        // Levenshtein distance check with higher tolerance for addresses
        $distance = levenshtein($normalizedContractor, $normalizedRegistry);

        return $distance <= max(2, strlen($normalizedContractor) * 0.2); // 20% tolerance
    }

    /**
     * Compare cities with fuzzy matching.
     */
    private function compareCities(string $contractorCity, string $registryCity): bool
    {
        $normalizedContractor = $this->normalizeString($contractorCity);
        $normalizedRegistry   = $this->normalizeString($registryCity);

        // Exact match after normalization
        if ($normalizedContractor === $normalizedRegistry) {
            return true;
        }

        // Levenshtein distance check
        $distance = levenshtein($normalizedContractor, $normalizedRegistry);

        return $distance <= 2;
    }

    /**
     * Compare postal codes (should be exact match after normalization).
     */
    private function comparePostalCodes(string $contractorPostalCode, string $registryPostalCode): bool
    {
        $normalizedContractor = preg_replace('/[^0-9]/', '', $contractorPostalCode);
        $normalizedRegistry   = preg_replace('/[^0-9]/', '', $registryPostalCode);

        return $normalizedContractor === $normalizedRegistry;
    }

    /**
     * Normalize string by removing special characters and converting to lowercase.
     */
    private function normalizeString(string $input): string
    {
        // Remove special characters and normalize spacing
        $normalized = preg_replace('/[^\p{L}\p{N}\s]/u', '', $input);

        // Convert to lowercase and remove extra spaces
        return trim(preg_replace('/\s+/', ' ', strtolower($normalized)));
    }

    /**
     * Normalize VAT ID by removing country prefix and special characters.
     */
    private function normalizeVatId(string $vatId): string
    {
        // Remove country prefix if present (e.g., "PL" from "PL1234567890")
        $normalized = preg_replace('/^[A-Z]{2}/', '', strtoupper($vatId));

        // Remove any non-digit characters
        return preg_replace('/[^0-9]/', '', $normalized);
    }

    /**
     * Normalize IBAN by removing spaces and converting to uppercase.
     */
    private function normalizeIban(string $iban): string
    {
        return strtoupper(preg_replace('/\s+/', '', $iban));
    }
}
