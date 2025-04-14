<?php

namespace App\Domain\Auth\DTOs;

class RegisterUserDTO
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        public readonly string $password,
        public readonly ?string $description = null,
        public readonly ?string $birthDate = null,
        public readonly ?string $phone = null,
    ) {
    }

    /**
     * Create a new DTO from an array of data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            email: $data['email'],
            password: $data['password'],
            description: $data['description'] ?? null,
            birthDate: $data['birth_date'] ?? null,
            phone: $data['phone'] ?? null,
        );
    }
}
