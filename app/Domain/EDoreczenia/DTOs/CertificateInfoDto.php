<?php

namespace App\Domain\EDoreczenia\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;
use Carbon\Carbon;

class CertificateInfoDto extends BaseDataDTO
{
    public function __construct(
        public readonly string $filePath,
        public readonly string $password,
        public readonly string $fingerprint,
        public readonly string $subjectCn,
        public readonly Carbon $validFrom,
        public readonly Carbon $validTo,
    ) {
    }

    public function toArray(): array
    {
        return [
            'filePath'    => $this->filePath,
            'password'    => $this->password,
            'fingerprint' => $this->fingerprint,
            'subjectCn'   => $this->subjectCn,
            'validFrom'   => $this->validFrom->toIso8601String(),
            'validTo'     => $this->validTo->toIso8601String(),
        ];
    }
}
