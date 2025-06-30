<?php

namespace App\Services\Signatures\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;

final class CertificateDTO extends BaseDataDTO
{
    public function __construct(
        public string $issuer,
        public string $serialNumber,
        public ?string $validFrom = null,
        public ?string $validTo = null,
        public ?string $subject = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'issuer'       => $this->issuer,
            'serialNumber' => $this->serialNumber,
            'validFrom'    => $this->validFrom,
            'validTo'      => $this->validTo,
            'subject'      => $this->subject,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            $data['issuer'] ?? '',
            $data['serialNumber'] ?? '',
            $data['validFrom'] ?? null,
            $data['validTo'] ?? null,
            $data['subject'] ?? null,
        );
    }
}
