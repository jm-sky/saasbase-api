<?php

namespace App\Domain\Common\DTOs;

/**
 * @property bool $mf
 * @property bool $regon
 * @property bool $vies
 */
class CommonCompanyLookupSources extends BaseDataDTO
{
    public function __construct(
        public bool $mf = false,
        public bool $regon = false,
        public bool $vies = false,
    ) {
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
