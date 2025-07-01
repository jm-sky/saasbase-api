<?php

namespace App\Domain\EDoreczenia\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;
use Carbon\Carbon;

final class CertificateInfoDto extends BaseDataDTO
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

    public static function fromArray(array $data): static
    {
        return new self(
            filePath: $data['filePath'],
            password: $data['password'],
            fingerprint: $data['fingerprint'],
            subjectCn: $data['subjectCn'],
            validFrom: $data['validFrom'],
            validTo: $data['validTo'],
        );
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
