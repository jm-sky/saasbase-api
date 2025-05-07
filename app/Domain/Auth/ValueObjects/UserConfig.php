<?php

namespace App\Domain\Auth\ValueObjects;

class UserConfig
{
    public function __construct(
        public bool $isPhonePublic = false,
        public bool $isEmailPublic = false,
        public bool $isBirthDatePublic = false,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            isPhonePublic: $data['isPhonePublic'] ?? false,
            isEmailPublic: $data['isEmailPublic'] ?? false,
            isBirthDatePublic: $data['isBirthDatePublic'] ?? false,
        );
    }

    public function toArray(): array
    {
        return [
            'isPhonePublic'     => $this->isPhonePublic,
            'isEmailPublic'     => $this->isEmailPublic,
            'isBirthDatePublic' => $this->isBirthDatePublic,
        ];
    }
}
