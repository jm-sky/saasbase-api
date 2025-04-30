<?php

namespace App\Domain\Users\DTOs;

use App\Domain\Auth\Models\User;
use Spatie\LaravelData\Data;

/**
 * @property ?string $id        UUID
 * @property string  $firstName
 * @property string  $lastName
 * @property string  $email
 * @property ?string $phone
 */
class PublicUserDTO extends Data
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        public readonly ?string $phone,
        public readonly ?string $id = null,
    ) {
    }

    public static function fromModel(User $user): self
    {
        return new self(
            firstName: $user->firstName,
            lastName: $user->lastName,
            email: $user->email,
            id: $user->id,
            phone: $user->phone,
        );
    }

    public static function fromArray(array $user): self
    {
        return new self(
            firstName: $user['first_name'],
            lastName: $user['last_name'],
            email: $user['email'],
            id: $user['id'],
            phone: $user['phone'],
        );
    }
}
