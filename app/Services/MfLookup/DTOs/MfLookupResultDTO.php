<?php

namespace App\Services\MfLookup\DTOs;

use App\Domain\Common\DTOs\AddressDTO;
use App\Domain\Common\DTOs\BankAccountDTO;
use App\Domain\Common\DTOs\CommonCompanyLookupData;
use App\Domain\Common\Enums\AddressType;
use App\Services\MfLookup\Enums\VatStatusEnum;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Ministry of Finance Lookup Result Data Transfer Object.
 *
 * @property string               $name                  Example: "Example Company Sp. z o.o."
 * @property string               $nip                   Example: "1234567890"
 * @property ?string              $regon                 Example: "123456789"
 * @property ?string              $krs                   Example: "0000123456"
 * @property ?string              $residenceAddress      Example: "ul. Kwiatowa 15, 00-001 Warszawa"
 * @property ?string              $workingAddress        Example: "ul. Słoneczna 7, 00-002 Warszawa"
 * @property string[]             $accountNumbers        Example: ["PL10105000997603123456789123", "PL60102010260000160201111111"]
 * @property VatStatusEnum        $vatStatus             Example: VatStatusEnum::ACTIVE
 * @property bool                 $hasVirtualAccounts    Example: false
 * @property RepresentativeDTO[]  $representatives       Example: [{"name": "Jan Kowalski", "nip": null, "pesel": "85010112345"}]
 * @property AuthorizedClerkDTO[] $authorizedClerks      Example: [{"name": "Anna Nowak", "nip": null, "pesel": "90020256789"}]
 * @property PartnerDTO[]         $partners              Example: [{"name": "Michał Wiśniewski", "nip": "9876543210", "pesel": null}]
 * @property ?string              $registrationLegalDate Example: "2015-01-01"
 * @property ?bool                $cache
 */
class MfLookupResultDTO implements Arrayable, \JsonSerializable
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
        /** @var RepresentativeDTO[] */
        public readonly array $representatives,
        /** @var AuthorizedClerkDTO[] */
        public readonly array $authorizedClerks,
        /** @var PartnerDTO[] */
        public readonly array $partners,
        public readonly ?string $registrationLegalDate,
        public readonly ?bool $cache = null,
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
            representatives: RepresentativeDTO::collect($data['representatives'] ?? []),
            authorizedClerks: AuthorizedClerkDTO::collect($data['authorizedClerks'] ?? []),
            partners: PartnerDTO::collect($data['partners'] ?? []),
            registrationLegalDate: $data['registrationLegalDate'] ?? null,
            cache: null,
        );
    }

    public function toArray(): array
    {
        return [
            'name'                  => $this->name,
            'nip'                   => $this->nip,
            'regon'                 => $this->regon,
            'krs'                   => $this->krs,
            'residenceAddress'      => $this->residenceAddress,
            'workingAddress'        => $this->workingAddress,
            'accountNumbers'        => $this->accountNumbers,
            'vatStatus'             => $this->vatStatus,
            'hasVirtualAccounts'    => $this->hasVirtualAccounts,
            'representatives'       => $this->representatives,
            'authorizedClerks'      => $this->authorizedClerks,
            'partners'              => $this->partners,
            'registrationLegalDate' => $this->registrationLegalDate,
            'cache'                 => $this->cache,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    protected function getAddress(): ?AddressDTO
    {
        $address = null;

        if ($this->workingAddress) {
            $mfAddress = MfAddressDTO::fromString($this->workingAddress);

            if ($mfAddress) {
                $address = new AddressDTO(
                    country: 'PL',
                    city: $mfAddress->city,
                    type: AddressType::REGISTERED_OFFICE,
                    isDefault: true,
                    street: $mfAddress->street . ' ' . $mfAddress->buildingAndFlat,
                    postalCode: $mfAddress->postalCode
                );
            }
        }

        if (!$address && $this->residenceAddress) {
            $mfAddress = MfAddressDTO::fromString($this->residenceAddress);

            if ($mfAddress) {
                $address = new AddressDTO(
                    country: 'PL',
                    city: $mfAddress->city,
                    type: AddressType::RESIDENCE,
                    isDefault: true,
                    street: $mfAddress->street . ' ' . $mfAddress->buildingAndFlat,
                    postalCode: $mfAddress->postalCode
                );
            }
        }

        return $address;
    }

    protected function getBankAccount(): ?BankAccountDTO
    {
        if (empty($this->accountNumbers)) {
            return null;
        }

        return new BankAccountDTO(
            iban: $this->accountNumbers[0],
            isDefault: true
        );
    }

    public function toCommonLookupData(): CommonCompanyLookupData
    {
        $address     = $this->getAddress();
        $bankAccount = $this->getBankAccount();

        return new CommonCompanyLookupData(
            name: $this->name,
            country: 'PL',
            vatId: $this->nip,
            regon: $this->regon,
            address: $address,
            bankAccount: $bankAccount,
            cache: $this->cache,
        );
    }
}
