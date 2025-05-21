<?php

namespace App\Domain\Auth\DTOs;

use App\Domain\Auth\Models\User;
use App\Domain\Common\DTOs\BaseDTO;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends BaseDTO<User>
 */
class RegisterUserDTO extends BaseDTO
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
    public static function fromArray(array $data): static
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

    /**
     * Create a DTO instance from a model.
     * Note: This method is implemented to satisfy the interface but should not typically be used
     * for registration DTOs as they are input-only DTOs.
     *
     * @param User $model
     *
     * @throws \InvalidArgumentException
     */
    public static function fromModel(Model $model): static
    {
        /* @var User $model */
        throw new \InvalidArgumentException('RegisterUserDTO is an input-only DTO and cannot be created from a model');
    }

    public function toArray(): array
    {
        return [
            'first_name'  => $this->firstName,
            'last_name'   => $this->lastName,
            'email'       => $this->email,
            'password'    => $this->password,
            'description' => $this->description,
            'birth_date'  => $this->birthDate,
            'phone'       => $this->phone,
        ];
    }
}
