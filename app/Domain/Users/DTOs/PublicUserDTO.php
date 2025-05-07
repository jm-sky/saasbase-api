<?php

namespace App\Domain\Users\DTOs;

use App\Domain\Auth\Models\User;
use App\Domain\Common\DTOs\BaseDTO;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends BaseDTO<User>
 *
 * @property ?string $id        UUID
 * @property string  $firstName
 * @property string  $lastName
 * @property ?string $email
 * @property ?string $phone
 */
class PublicUserDTO extends BaseDTO
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly ?string $id = null,
    ) {
    }

    public static function fromModel(Model $model): static
    {
        /* @var User $model */
        return new static(
            firstName: $model->first_name,
            lastName: $model->last_name,
            email: $model->public_email,
            phone: $model->public_phone,
            id: $model->id,
        );
    }

    public static function fromArray(array $data): static
    {
        return new static(
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            id: $data['id'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'        => $this->id,
            'firstName' => $this->firstName,
            'lastName'  => $this->lastName,
            'email'     => $this->email,
            'phone'     => $this->phone,
        ];
    }
}
