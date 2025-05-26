<?php

namespace App\Services\GusLookup\DTOs;

use Illuminate\Contracts\Support\Arrayable;

/**
 * GUS Lookup Result Data Transfer Object.
 *
 * @property string  $name             Example: "Example Company Sp. z o.o."
 * @property string  $regon            Example: "123456789"
 * @property ?string $nip              Example: "1234567890"
 * @property ?string $krs              Example: "0000123456"
 * @property ?string $residenceAddress Example: "ul. Kwiatowa 15, 00-001 Warszawa"
 * @property ?string $workingAddress   Example: "ul. Słoneczna 7, 00-002 Warszawa"
 * @property ?string $registrationDate Example: "2015-01-01"
 * @property ?string $startDate        Example: "2015-01-01"
 * @property ?string $endDate          Example: "2023-12-31"
 * @property ?string $suspensionDate   Example: "2023-01-01"
 * @property ?string $resumptionDate   Example: "2023-02-01"
 * @property ?string $mainPkdCode      Example: "6201.Z"
 * @property ?string $mainPkdName      Example: "Działalność związana z oprogramowaniem"
 * @property array   $pkdCodes         Example: ["6201.Z", "6202.Z"]
 * @property array   $pkdNames         Example: ["Działalność związana z oprogramowaniem", "Działalność związana z doradztwem w zakresie IT"]
 */
class GusLookupResultDTO implements Arrayable, \JsonSerializable
{
    public function __construct(
        public readonly string $name,
        public readonly string $regon,
        public readonly ?string $nip,
        public readonly ?string $krs,
        public readonly ?string $residenceAddress,
        public readonly ?string $workingAddress,
        public readonly ?string $registrationDate,
        public readonly ?string $startDate,
        public readonly ?string $endDate,
        public readonly ?string $suspensionDate,
        public readonly ?string $resumptionDate,
        public readonly ?string $mainPkdCode,
        public readonly ?string $mainPkdName,
        public readonly array $pkdCodes,
        public readonly array $pkdNames,
    ) {
    }

    public static function fromApiResponse(array $data): self
    {
        return new self(
            name: $data['name'] ?? '',
            regon: $data['regon'] ?? '',
            nip: $data['nip'] ?? null,
            krs: $data['krs'] ?? null,
            residenceAddress: $data['residenceAddress'] ?? null,
            workingAddress: $data['workingAddress'] ?? null,
            registrationDate: $data['registrationDate'] ?? null,
            startDate: $data['startDate'] ?? null,
            endDate: $data['endDate'] ?? null,
            suspensionDate: $data['suspensionDate'] ?? null,
            resumptionDate: $data['resumptionDate'] ?? null,
            mainPkdCode: $data['mainPkdCode'] ?? null,
            mainPkdName: $data['mainPkdName'] ?? null,
            pkdCodes: $data['pkdCodes'] ?? [],
            pkdNames: $data['pkdNames'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'name'             => $this->name,
            'regon'            => $this->regon,
            'nip'              => $this->nip,
            'krs'              => $this->krs,
            'residenceAddress' => $this->residenceAddress,
            'workingAddress'   => $this->workingAddress,
            'registrationDate' => $this->registrationDate,
            'startDate'        => $this->startDate,
            'endDate'          => $this->endDate,
            'suspensionDate'   => $this->suspensionDate,
            'resumptionDate'   => $this->resumptionDate,
            'mainPkdCode'      => $this->mainPkdCode,
            'mainPkdName'      => $this->mainPkdName,
            'pkdCodes'         => $this->pkdCodes,
            'pkdNames'         => $this->pkdNames,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
