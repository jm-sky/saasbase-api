<?php

namespace App\Services\Signatures\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;

final class SignerIdentityDTO extends BaseDataDTO
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $pesel,
        public ?string $middleName = null,
        public ?string $trustedProfileId = null,
        public ?string $epuapUsername = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'firstName'        => $this->firstName,
            'lastName'         => $this->lastName,
            'pesel'            => $this->pesel,
            'middleName'       => $this->middleName,
            'trustedProfileId' => $this->trustedProfileId,
            'epuapUsername'    => $this->epuapUsername,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            $data['firstName'] ?? '',
            $data['lastName'] ?? '',
            $data['pesel'] ?? '',
            $data['middleName'] ?? null,
            $data['trustedProfileId'] ?? null,
            $data['epuapUsername'] ?? null,
        );
    }
}
