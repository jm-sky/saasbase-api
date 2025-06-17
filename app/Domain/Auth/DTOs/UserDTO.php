<?php

namespace App\Domain\Auth\DTOs;

use App\Domain\Auth\Models\User;
use App\Domain\Common\DTOs\BaseDTO;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends BaseDTO<User>
 *
 * @property string  $firstName
 * @property string  $lastName
 * @property string  $email
 * @property ?string $id                 UUID
 * @property ?string $description
 * @property ?string $birthDate
 * @property ?string $phone
 * @property bool    $isAdmin
 * @property bool    $isEmailVerified
 * @property bool    $isTwoFactorEnabled
 * @property array   $roles
 * @property array   $permissions
 * @property ?Carbon $createdAt          Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $updatedAt          Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $deletedAt          Internally Carbon, accepts/serializes ISO 8601
 */
final class UserDTO extends BaseDTO
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        public readonly ?string $id = null,
        public readonly ?string $description = null,
        public readonly ?string $birthDate = null,
        public readonly ?string $phone = null,
        public readonly bool $isAdmin = false,
        public readonly bool $isEmailVerified = false,
        public readonly bool $isTwoFactorEnabled = false,
        public readonly array $roles = [],
        public readonly array $permissions = [],
        public ?Carbon $createdAt = null,
        public ?Carbon $updatedAt = null,
        public ?Carbon $deletedAt = null,
    ) {
    }

    public static function fromModel(Model $model): static
    {
        /* @var User $model */
        return new self(
            firstName: $model->first_name,
            lastName: $model->last_name,
            email: $model->email,
            id: $model->id,
            description: $model->description,
            birthDate: $model->birth_date,
            phone: $model->phone,
            isAdmin: $model->is_admin,
            isEmailVerified: $model->isEmailVerified(),
            isTwoFactorEnabled: $model->isTwoFactorEnabled(),
            roles: $model->getRoleNames()->toArray(),
            permissions: $model->getAllPermissions()->pluck('name')->toArray(),
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
            deletedAt: $model->deleted_at,
        );
    }

    public static function fromArray(array $data): static
    {
        return new static(
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            email: $data['email'],
            id: $data['id'] ?? null,
            description: $data['description'] ?? null,
            birthDate: $data['birth_date'] ?? null,
            phone: $data['phone'] ?? null,
            isAdmin: $data['is_admin'] ?? false,
            isEmailVerified: $data['is_email_verified'] ?? false,
            isTwoFactorEnabled: $data['is_two_factor_enabled'] ?? false,
            roles: $data['roles'] ?? [],
            permissions: $data['permissions'] ?? [],
            createdAt: isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? Carbon::parse($data['updated_at']) : null,
            deletedAt: isset($data['deleted_at']) ? Carbon::parse($data['deleted_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'                 => $this->id,
            'firstName'          => $this->firstName,
            'lastName'           => $this->lastName,
            'email'              => $this->email,
            'description'        => $this->description,
            'birthDate'          => $this->birthDate,
            'phone'              => $this->phone,
            'isAdmin'            => $this->isAdmin,
            'isEmailVerified'    => $this->isEmailVerified,
            'isTwoFactorEnabled' => $this->isTwoFactorEnabled,
            'roles'              => $this->roles,
            'permissions'        => $this->permissions,
            'createdAt'          => $this->createdAt?->toIso8601String(),
            'updatedAt'          => $this->updatedAt?->toIso8601String(),
            'deletedAt'          => $this->deletedAt?->toIso8601String(),
        ];
    }
}
