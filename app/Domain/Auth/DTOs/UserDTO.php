<?php

namespace App\Domain\Auth\DTOs;

use App\Domain\Auth\Models\User;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

/**
 * @property ?string $id UUID
 * @property string $firstName
 * @property string $lastName
 * @property string $email
 * @property ?string $avatarUrl
 * @property ?string $description
 * @property ?string $birthDate
 * @property ?string $phone
 * @property bool $isAdmin
 * @property ?Carbon $createdAt Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $updatedAt Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $deletedAt Internally Carbon, accepts/serializes ISO 8601
 */
class UserDTO extends Data
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        public readonly ?string $id = null,
        public readonly ?string $avatarUrl = null,
        public readonly ?string $description = null,
        public readonly ?string $birthDate = null,
        public readonly ?string $phone = null,
        public readonly bool $isAdmin = false,
        #[WithCast(DateTimeInterfaceCast::class, format: \DateTimeInterface::ATOM)]
        public ?Carbon $createdAt = null,
        #[WithCast(DateTimeInterfaceCast::class, format: \DateTimeInterface::ATOM)]
        public ?Carbon $updatedAt = null,
        #[WithCast(DateTimeInterfaceCast::class, format: \DateTimeInterface::ATOM)]
        public ?Carbon $deletedAt = null,
    ) {
        // Convert string timestamps to Carbon objects if needed
        if (is_string($this->createdAt)) {
            $this->createdAt = Carbon::parse($this->createdAt);
        }
        if (is_string($this->updatedAt)) {
            $this->updatedAt = Carbon::parse($this->updatedAt);
        }
        if (is_string($this->deletedAt)) {
            $this->deletedAt = Carbon::parse($this->deletedAt);
        }
    }

    public static function fromModel(User $model): self
    {
        return new self(
            firstName: $model->first_name,
            lastName: $model->last_name,
            email: $model->email,
            id: $model->id,
            avatarUrl: $model->avatar_url,
            description: $model->description,
            birthDate: $model->birth_date,
            phone: $model->phone,
            isAdmin: $model->is_admin,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
            deletedAt: $model->deleted_at,
        );
    }
}
