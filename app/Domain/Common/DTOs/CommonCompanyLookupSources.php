<?php

namespace App\Domain\Common\DTOs;

/**
 * @property bool $mf
 * @property bool $regon
 * @property bool $vies
 */
final class CommonCompanyLookupSources extends BaseDataDTO
{
    public function __construct(
        public bool $mf = false,
        public bool $regon = false,
        public bool $vies = false,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            mf: $data['mf'],
            regon: $data['regon'],
            vies: $data['vies'],
        );
    }

    public function toArray(): array
    {
        return [
            'mf'    => $this->mf,
            'regon' => $this->regon,
            'vies'  => $this->vies,
        ];
    }
}
