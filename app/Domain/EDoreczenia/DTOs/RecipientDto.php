<?php

namespace App\Domain\EDoreczenia\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;

final class RecipientDto extends BaseDataDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $name,
        public readonly ?string $identifier = null,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            email: $data['email'],
            name: $data['name'],
            identifier: $data['identifier'],
        );
    }

    public function toArray(): array
    {
        return [
            'email'      => $this->email,
            'name'       => $this->name,
            'identifier' => $this->identifier,
        ];
    }
}
