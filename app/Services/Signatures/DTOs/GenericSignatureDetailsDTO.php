<?php

namespace App\Services\Signatures\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;

final class GenericSignatureDetailsDTO extends BaseDataDTO
{
    public function __construct(
        public bool $valid,
        public bool $trustedCA,
        public ?SignerIdentityDTO $signerIdentity = null,
        public ?CertificateDTO $certificate = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'valid'          => $this->valid,
            'trustedCA'      => $this->trustedCA,
            'signerIdentity' => $this->signerIdentity?->toArray(),
            'certificate'    => $this->certificate?->toArray(),
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            $data['valid'] ?? false,
            $data['trustedCA'] ?? false,
            $data['signerIdentity'] ?? null,
            $data['certificate'] ?? null,
        );
    }
}
