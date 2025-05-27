<?php

namespace App\Services\GusLookup\DTOs;

use App\Domain\Common\DTOs\AddressDTO;
use App\Domain\Common\DTOs\CommonCompanyLookupData;
use App\Domain\Common\Enums\AddressType;
use Illuminate\Contracts\Support\Arrayable;

class GusFullReportResultDTO implements Arrayable, \JsonSerializable
{
    public function __construct(
        public string $regon,
        public string $nip,
        public ?string $nipStatus,
        public string $name,
        public ?string $shortName,
        public string $registrationNumber,
        public string $registrationDate,
        public string $establishmentDate,
        public string $businessStartDate,
        public ?string $regonRegistrationDate,
        public ?string $businessSuspensionDate,
        public ?string $businessResumptionDate,
        public string $lastChangeDate,
        public ?string $businessEndDate,
        public ?string $regonDeletionDate,
        public ?string $bankruptcyDeclarationDate,
        public ?string $bankruptcyEndDate,
        public string $countryCode,
        public string $provinceCode,
        public string $countyCode,
        public string $municipalityCode,
        public string $postalCode,
        public string $postalCityCode,
        public string $cityCode,
        public string $streetCode,
        public string $buildingNumber,
        public ?string $apartmentNumber,
        public ?string $unusualLocation,
        public string $phoneNumber,
        public ?string $internalPhoneNumber,
        public ?string $faxNumber,
        public ?string $email,
        public ?string $website,
        public string $countryName,
        public string $provinceName,
        public string $countyName,
        public string $municipalityName,
        public string $cityName,
        public string $postalCityName,
        public string $streetName,
        public string $legalFormCode,
        public string $detailedLegalFormCode,
        public string $financingFormCode,
        public string $ownershipFormCode,
        public ?string $foundingBodyCode,
        public string $registrationAuthorityCode,
        public string $registryTypeCode,
        public string $legalFormName,
        public string $detailedLegalFormName,
        public string $financingFormName,
        public string $ownershipFormName,
        public ?string $foundingBodyName,
        public string $registrationAuthorityName,
        public string $registryTypeName,
        public int $localUnitsCount,
    ) {
    }

    public function toArray(): array
    {
        return [
            'regon'                     => $this->regon,
            'nip'                       => $this->nip,
            'nipStatus'                 => $this->nipStatus,
            'name'                      => $this->name,
            'shortName'                 => $this->shortName,
            'registrationNumber'        => $this->registrationNumber,
            'registrationDate'          => $this->registrationDate,
            'establishmentDate'         => $this->establishmentDate,
            'businessStartDate'         => $this->businessStartDate,
            'regonRegistrationDate'     => $this->regonRegistrationDate,
            'businessSuspensionDate'    => $this->businessSuspensionDate,
            'businessResumptionDate'    => $this->businessResumptionDate,
            'lastChangeDate'            => $this->lastChangeDate,
            'businessEndDate'           => $this->businessEndDate,
            'regonDeletionDate'         => $this->regonDeletionDate,
            'bankruptcyDeclarationDate' => $this->bankruptcyDeclarationDate,
            'bankruptcyEndDate'         => $this->bankruptcyEndDate,
            'countryCode'               => $this->countryCode,
            'provinceCode'              => $this->provinceCode,
            'countyCode'                => $this->countyCode,
            'municipalityCode'          => $this->municipalityCode,
            'postalCode'                => $this->postalCode,
            'postalCityCode'            => $this->postalCityCode,
            'cityCode'                  => $this->cityCode,
            'streetCode'                => $this->streetCode,
            'buildingNumber'            => $this->buildingNumber,
            'apartmentNumber'           => $this->apartmentNumber,
            'unusualLocation'           => $this->unusualLocation,
            'phoneNumber'               => $this->phoneNumber,
            'internalPhoneNumber'       => $this->internalPhoneNumber,
            'faxNumber'                 => $this->faxNumber,
            'email'                     => $this->email,
            'website'                   => $this->website,
            'countryName'               => $this->countryName,
            'provinceName'              => $this->provinceName,
            'countyName'                => $this->countyName,
            'municipalityName'          => $this->municipalityName,
            'cityName'                  => $this->cityName,
            'postalCityName'            => $this->postalCityName,
            'streetName'                => $this->streetName,
            'legalFormCode'             => $this->legalFormCode,
            'detailedLegalFormCode'     => $this->detailedLegalFormCode,
            'financingFormCode'         => $this->financingFormCode,
            'ownershipFormCode'         => $this->ownershipFormCode,
            'foundingBodyCode'          => $this->foundingBodyCode,
            'registrationAuthorityCode' => $this->registrationAuthorityCode,
            'registryTypeCode'          => $this->registryTypeCode,
            'legalFormName'             => $this->legalFormName,
            'detailedLegalFormName'     => $this->detailedLegalFormName,
            'financingFormName'         => $this->financingFormName,
            'ownershipFormName'         => $this->ownershipFormName,
            'foundingBodyName'          => $this->foundingBodyName,
            'registrationAuthorityName' => $this->registrationAuthorityName,
            'registryTypeName'          => $this->registryTypeName,
            'localUnitsCount'           => $this->localUnitsCount,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toCommonLookupData(): CommonCompanyLookupData
    {
        $address = new AddressDTO(
            country: $this->countryCode,
            city: $this->cityName,
            type: AddressType::REGISTERED_OFFICE,
            isDefault: true,
            street: $this->streetName,
            building: $this->buildingNumber,
            flat: $this->apartmentNumber,
            postalCode: $this->postalCode
        );

        return new CommonCompanyLookupData(
            name: $this->name,
            country: $this->countryCode,
            vatId: $this->nip,
            regon: $this->regon,
            shortName: $this->shortName,
            phoneNumber: $this->phoneNumber,
            email: $this->email,
            website: $this->website,
            address: $address,
            bankAccount: null // GUS doesn't provide bank account information
        );
    }
}
