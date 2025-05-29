<?php

namespace App\Services\RegonLookup\DTOs;

use App\Domain\Common\DTOs\AddressDTO;
use App\Domain\Common\DTOs\CommonCompanyLookupData;
use App\Domain\Common\Enums\AddressType;
use Illuminate\Contracts\Support\Arrayable;

/**
 * @property string  $regon                     | key: fiz_regon9                                    | Example: 366908598
 * @property ?string $nip                       | key: praw_nip                                       | Example: 1251402446
 * @property string  $name                      | key: fiz_nazwa                                     | Example: IT Services & Solutions Piotr Lotka
 * @property string  $shortName                 | key: fiz_nazwaSkrocona                             | Example: IT Services & Solutions
 * @property string  $establishmentDate         | key: fiz_dataPowstania                             | Example: 2017-03-29
 * @property string  $businessStartDate         | key: fiz_dataRozpoczeciaDzialalnosci               | Example: 2017-04-03
 * @property string  $regonRegistrationDate     | key: fiz_dataWpisuDzialalnosciDoRegon              | Example: 2017-03-29
 * @property string  $businessSuspensionDate    | key: fiz_dataZawieszeniaDzialalnosci               | Example:
 * @property string  $businessResumptionDate    | key: fiz_dataWznowieniaDzialalnosci                | Example:
 * @property string  $lastChangeDate            | key: fiz_dataZaistnieniaZmianyDzialalnosci         | Example: 2017-05-04
 * @property string  $businessEndDate           | key: fiz_dataZakonczeniaDzialalnosci               | Example:
 * @property string  $regonDeletionDate         | key: fiz_dataSkresleniaDzialalnosciZRegon          | Example:
 * @property string  $bankruptcyDeclarationDate | key: fiz_dataOrzeczeniaOUpadlosci                  | Example:
 * @property string  $bankruptcyEndDate         | key: fiz_dataZakonczeniaPostepowaniaUpadlosciowego | Example:
 * @property string  $countryCode               | key: fiz_adSiedzKraj_Symbol                        | Example: PL
 * @property string  $provinceCode              | key: fiz_adSiedzWojewodztwo_Symbol                 | Example: 14
 * @property string  $countyCode                | key: fiz_adSiedzPowiat_Symbol                      | Example: 14
 * @property string  $municipalityCode          | key: fiz_adSiedzGmina_Symbol                       | Example: 011
 * @property string  $postalCode                | key: fiz_adSiedzKodPocztowy                        | Example: 05160
 * @property string  $postalCityCode            | key: fiz_adSiedzMiejscowoscPoczty_Symbol           | Example: 0921148
 * @property string  $cityCode                  | key: fiz_adSiedzMiejscowosc_Symbol                 | Example: 0921148
 * @property string  $streetCode                | key: fiz_adSiedzUlica_Symbol                       | Example: 11208
 * @property string  $buildingNumber            | key: fiz_adSiedzNumerNieruchomosci                 | Example: 324
 * @property string  $apartmentNumber           | key: fiz_adSiedzNumerLokalu                        | Example: 31
 * @property string  $unusualLocation           | key: fiz_adSiedzNietypoweMiejsceLokalizacji        | Example:
 * @property string  $phoneNumber               | key: fiz_numerTelefonu                             | Example:
 * @property string  $internalPhoneNumber       | key: fiz_numerWewnetrznyTelefonu                   | Example:
 * @property string  $faxNumber                 | key: fiz_numerFaksu                                | Example:
 * @property string  $email                     | key: fiz_adresEmail                                | Example:
 * @property string  $website                   | key: fiz_adresStronyinternetowej                   | Example:
 * @property string  $countryName               | key: fiz_adSiedzKraj_Nazwa                         | Example: POLSKA
 * @property string  $provinceName              | key: fiz_adSiedzWojewodztwo_Nazwa                  | Example: MAZOWIECKIE
 * @property string  $countyName                | key: fiz_adSiedzPowiat_Nazwa                       | Example: nowodworski
 * @property string  $municipalityName          | key: fiz_adSiedzGmina_Nazwa                        | Example: Nowy Dwór Mazowiecki
 * @property string  $cityName                  | key: fiz_adSiedzMiejscowosc_Nazwa                  | Example: Nowy Dwór Mazowiecki
 * @property string  $postalCityName            | key: fiz_adSiedzMiejscowoscPoczty_Nazwa            | Example: Nowy Dwór Mazowiecki
 * @property string  $streetName                | key: fiz_adSiedzUlica_Nazwa                        | Example: ul. 29 Listopada
 * @property string  $registrationNumber        | key: fizC_numerWRejestrzeEwidencji                 | Example: 000543990/2017
 * @property string  $registrationDate          | key: fizC_dataWpisuDoRejestruEwidencji              | Example: 2017-03-29
 * @property string  $registrationDeletionDate  | key: fizC_dataSkresleniaZRejestruEwidencji         | Example:
 * @property string  $registrationAuthorityCode | key: fizC_OrganRejestrowy_Symbol                   | Example: 120000000
 * @property string  $registrationAuthorityName | key: fizC_OrganRejestrowy_Nazwa                    | Example: MINISTER ROZWOJU
 * @property string  $registryTypeCode          | key: fizC_RodzajRejestru_Symbol                    | Example: 151
 * @property string  $registryTypeName          | key: fizC_RodzajRejestru_Nazwa                     | Example: CENTRALNA EWIDENCJA I INFORMACJA O DZIAŁALNOŚCI GOSPODARCZEJ
 * @property bool    $hasNotStartedActivity     | key: fizC_NiePodjetoDzialalnosci                   | Example: false
 */
class RegonReportForNaturalPerson implements Arrayable, \JsonSerializable
{
    public function __construct(
        public readonly string $regon,
        public readonly string $name,
        public readonly ?string $nip,
        public readonly ?string $shortName,
        public readonly string $establishmentDate,
        public readonly string $businessStartDate,
        public readonly string $regonRegistrationDate,
        public readonly ?string $businessSuspensionDate,
        public readonly ?string $businessResumptionDate,
        public readonly string $lastChangeDate,
        public readonly ?string $businessEndDate,
        public readonly ?string $regonDeletionDate,
        public readonly ?string $bankruptcyDeclarationDate,
        public readonly ?string $bankruptcyEndDate,
        public readonly string $countryCode,
        public readonly string $provinceCode,
        public readonly string $countyCode,
        public readonly string $municipalityCode,
        public readonly string $postalCode,
        public readonly string $postalCityCode,
        public readonly string $cityCode,
        public readonly string $streetCode,
        public readonly string $buildingNumber,
        public readonly ?string $apartmentNumber,
        public readonly ?string $unusualLocation,
        public readonly ?string $phoneNumber,
        public readonly ?string $internalPhoneNumber,
        public readonly ?string $faxNumber,
        public readonly ?string $email,
        public readonly ?string $website,
        public readonly string $countryName,
        public readonly string $provinceName,
        public readonly string $countyName,
        public readonly string $municipalityName,
        public readonly string $cityName,
        public readonly string $postalCityName,
        public readonly string $streetName,
        public readonly string $registrationNumber,
        public readonly string $registrationDate,
        public readonly ?string $registrationDeletionDate,
        public readonly string $registrationAuthorityCode,
        public readonly string $registrationAuthorityName,
        public readonly string $registryTypeCode,
        public readonly string $registryTypeName,
        public readonly bool $hasNotStartedActivity,
    ) {
    }

    public static function fromXml(\SimpleXMLElement $xml, ?string $nip = null): static
    {
        return new static(
            nip: $nip ?? null,
            regon: (string) $xml->xpath('fiz_regon9')[0],
            name: (string) $xml->xpath('fiz_nazwa')[0],
            shortName: (string) $xml->xpath('fiz_nazwaSkrocona')[0] ?: null,
            establishmentDate: (string) $xml->xpath('fiz_dataPowstania')[0],
            businessStartDate: (string) $xml->xpath('fiz_dataRozpoczeciaDzialalnosci')[0],
            regonRegistrationDate: (string) $xml->xpath('fiz_dataWpisuDzialalnosciDoRegon')[0],
            businessSuspensionDate: (string) $xml->xpath('fiz_dataZawieszeniaDzialalnosci')[0] ?: null,
            businessResumptionDate: (string) $xml->xpath('fiz_dataWznowieniaDzialalnosci')[0] ?: null,
            lastChangeDate: (string) $xml->xpath('fiz_dataZaistnieniaZmianyDzialalnosci')[0],
            businessEndDate: (string) $xml->xpath('fiz_dataZakonczeniaDzialalnosci')[0] ?: null,
            regonDeletionDate: (string) $xml->xpath('fiz_dataSkresleniaDzialalnosciZRegon')[0] ?: null,
            bankruptcyDeclarationDate: (string) $xml->xpath('fiz_dataOrzeczeniaOUpadlosci')[0] ?: null,
            bankruptcyEndDate: (string) $xml->xpath('fiz_dataZakonczeniaPostepowaniaUpadlosciowego')[0] ?: null,
            countryCode: (string) $xml->xpath('fiz_adSiedzKraj_Symbol')[0],
            provinceCode: (string) $xml->xpath('fiz_adSiedzWojewodztwo_Symbol')[0],
            countyCode: (string) $xml->xpath('fiz_adSiedzPowiat_Symbol')[0],
            municipalityCode: (string) $xml->xpath('fiz_adSiedzGmina_Symbol')[0],
            postalCode: (string) $xml->xpath('fiz_adSiedzKodPocztowy')[0],
            postalCityCode: (string) $xml->xpath('fiz_adSiedzMiejscowoscPoczty_Symbol')[0],
            cityCode: (string) $xml->xpath('fiz_adSiedzMiejscowosc_Symbol')[0],
            streetCode: (string) $xml->xpath('fiz_adSiedzUlica_Symbol')[0],
            buildingNumber: (string) $xml->xpath('fiz_adSiedzNumerNieruchomosci')[0],
            apartmentNumber: (string) $xml->xpath('fiz_adSiedzNumerLokalu')[0] ?: null,
            unusualLocation: (string) $xml->xpath('fiz_adSiedzNietypoweMiejsceLokalizacji')[0] ?: null,
            phoneNumber: (string) $xml->xpath('fiz_numerTelefonu')[0] ?: null,
            internalPhoneNumber: (string) $xml->xpath('fiz_numerWewnetrznyTelefonu')[0] ?: null,
            faxNumber: (string) $xml->xpath('fiz_numerFaksu')[0] ?: null,
            email: (string) $xml->xpath('fiz_adresEmail')[0] ?: null,
            website: (string) $xml->xpath('fiz_adresStronyinternetowej')[0] ?: null,
            countryName: (string) $xml->xpath('fiz_adSiedzKraj_Nazwa')[0],
            provinceName: (string) $xml->xpath('fiz_adSiedzWojewodztwo_Nazwa')[0],
            countyName: (string) $xml->xpath('fiz_adSiedzPowiat_Nazwa')[0],
            municipalityName: (string) $xml->xpath('fiz_adSiedzGmina_Nazwa')[0],
            cityName: (string) $xml->xpath('fiz_adSiedzMiejscowosc_Nazwa')[0],
            postalCityName: (string) $xml->xpath('fiz_adSiedzMiejscowoscPoczty_Nazwa')[0],
            streetName: (string) $xml->xpath('fiz_adSiedzUlica_Nazwa')[0],
            registrationNumber: (string) $xml->xpath('fizC_numerWRejestrzeEwidencji')[0],
            registrationDate: (string) $xml->xpath('fizC_dataWpisuDoRejestruEwidencji')[0],
            registrationDeletionDate: (string) $xml->xpath('fizC_dataSkresleniaZRejestruEwidencji')[0] ?: null,
            registrationAuthorityCode: (string) $xml->xpath('fizC_OrganRejestrowy_Symbol')[0],
            registrationAuthorityName: (string) $xml->xpath('fizC_OrganRejestrowy_Nazwa')[0],
            registryTypeCode: (string) $xml->xpath('fizC_RodzajRejestru_Symbol')[0],
            registryTypeName: (string) $xml->xpath('fizC_RodzajRejestru_Nazwa')[0],
            hasNotStartedActivity: (bool) $xml->xpath('fizC_NiePodjetoDzialalnosci')[0],
        );
    }

    public function toArray(): array
    {
        return [
            'regon'                     => $this->regon,
            'nip'                       => $this->nip,
            'name'                      => $this->name,
            'shortName'                 => $this->shortName,
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
            'registrationNumber'        => $this->registrationNumber,
            'registrationDate'          => $this->registrationDate,
            'registrationDeletionDate'  => $this->registrationDeletionDate,
            'registrationAuthorityCode' => $this->registrationAuthorityCode,
            'registrationAuthorityName' => $this->registrationAuthorityName,
            'registryTypeCode'          => $this->registryTypeCode,
            'registryTypeName'          => $this->registryTypeName,
            'hasNotStartedActivity'     => $this->hasNotStartedActivity,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function getAddressAsString(): string
    {
        return implode(', ', array_filter([
            $this->cityName,
            $this->streetName,
            $this->buildingNumber,
            $this->apartmentNumber,
            $this->municipalityName,
            $this->provinceName,
            $this->countyName,
        ]));
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
            bankAccount: null // REGON doesn't provide bank account information
        );
    }

    public function toUnifiedReportDto(): RegonReportUnified
    {
        return new RegonReportUnified(
            regon: $this->regon,
            nip: null, // Natural persons don't have NIP
            nipStatus: null, // Natural persons don't have NIP status
            name: $this->name,
            shortName: $this->shortName,
            registrationNumber: $this->registrationNumber,
            registrationDate: $this->registrationDate,
            establishmentDate: $this->establishmentDate,
            businessStartDate: $this->businessStartDate,
            regonRegistrationDate: $this->regonRegistrationDate,
            businessSuspensionDate: $this->businessSuspensionDate,
            businessResumptionDate: $this->businessResumptionDate,
            lastChangeDate: $this->lastChangeDate,
            businessEndDate: $this->businessEndDate,
            regonDeletionDate: $this->regonDeletionDate,
            bankruptcyDeclarationDate: $this->bankruptcyDeclarationDate,
            bankruptcyEndDate: $this->bankruptcyEndDate,
            countryCode: $this->countryCode,
            provinceCode: $this->provinceCode,
            countyCode: $this->countyCode,
            municipalityCode: $this->municipalityCode,
            postalCode: $this->postalCode,
            postalCityCode: $this->postalCityCode,
            cityCode: $this->cityCode,
            streetCode: $this->streetCode,
            buildingNumber: $this->buildingNumber,
            apartmentNumber: $this->apartmentNumber,
            unusualLocation: $this->unusualLocation,
            phoneNumber: $this->phoneNumber,
            internalPhoneNumber: $this->internalPhoneNumber,
            faxNumber: $this->faxNumber,
            email: $this->email,
            website: $this->website,
            countryName: $this->countryName,
            provinceName: $this->provinceName,
            countyName: $this->countyName,
            municipalityName: $this->municipalityName,
            cityName: $this->cityName,
            postalCityName: $this->postalCityName,
            streetName: $this->streetName,
            registrationDeletionDate: $this->registrationDeletionDate,
            registrationAuthorityCode: $this->registrationAuthorityCode,
            registryTypeCode: $this->registryTypeCode,
            registrationAuthorityName: $this->registrationAuthorityName,
            registryTypeName: $this->registryTypeName,
            legalFormCode: null, // Natural persons don't have legal form
            detailedLegalFormCode: null,
            financingFormCode: null,
            ownershipFormCode: null,
            foundingBodyCode: null,
            legalFormName: null,
            detailedLegalFormName: null,
            financingFormName: null,
            ownershipFormName: null,
            foundingBodyName: null,
            localUnitsCount: null, // Natural persons don't have local units
            hasNotStartedActivity: $this->hasNotStartedActivity,
        );
    }
}
