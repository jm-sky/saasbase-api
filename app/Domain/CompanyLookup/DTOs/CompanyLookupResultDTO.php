<?php

namespace App\Domain\CompanyLookup\DTOs;

use App\Domain\CompanyLookup\Enums\VatStatusEnum;

/**
 * Data Transfer Object for Company Lookup Result
 *
 * Example:
 * - name: "Example Company Sp. z o.o."
 * - nip: "1234567890"
 * - regon: "0987654321"
 * - krs: "0000123456"
 * - residenceAddress: null
 * - workingAddress: "ul. Przykładowa 10, 00-001 Warszawa"
 * - accountNumbers: ["12345678901234567890123456"]
 * - vatStatus: VatStatusEnum::ACTIVE
 * - hasVirtualAccounts: false
 * - representatives: [
 *      ["name" => "Jan Kowalski", "nip" => null, "pesel" => "80010112345"]
 *   ]
 * - registrationLegalDate: "2010-05-12"
 */
class CompanyLookupResultDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $nip,
        public readonly ?string $regon,
        public readonly ?string $krs,
        public readonly ?string $residenceAddress,
        public readonly ?string $workingAddress,
        public readonly array $accountNumbers,
        public readonly VatStatusEnum $vatStatus,
        public readonly bool $hasVirtualAccounts,
        public readonly array $representatives,
        public readonly array $authorizedClerks,
        public readonly array $partners,
        public readonly ?string $registrationLegalDate,
    ) {
    }

    public static function fromApiResponse(array $data): self
    {
        return new self(
            name: $data['name'] ?? '',
            nip: $data['nip'] ?? '',
            regon: $data['regon'] ?? null,
            krs: $data['krs'] ?? null,
            residenceAddress: $data['residenceAddress'] ?? null,
            workingAddress: $data['workingAddress'] ?? null,
            accountNumbers: $data['accountNumbers'] ?? [],
            vatStatus: VatStatusEnum::fromString($data['statusVat'] ?? null),
            hasVirtualAccounts: $data['hasVirtualAccounts'] ?? false,
            representatives: $data['representatives'] ?? [],
            authorizedClerks: $data['authorizedClerks'] ?? [],
            partners: $data['partners'] ?? [],
            registrationLegalDate: $data['registrationLegalDate'] ?? null,
        );
    }
}
