<?php

namespace App\Domain\Users\DTOs;

use Carbon\Carbon;
use App\Domain\Auth\Models\User;
use App\Domain\Common\DTOs\BaseDTO;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends BaseDTO<User>
 *
 * @property ?string $id        UUID
 * @property string  $name
 * @property ?string $email
 * @property ?string $avatarUrl
 * @property ?Carbon $createdAt
 */
class UserPreviewDTO extends BaseDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $email,
        public readonly ?string $id = null,
        public readonly ?string $avatarUrl = null,
        public readonly ?Carbon $createdAt = null,
    ) {
    }

    public static function fromModel(Model $model): static
    {
        /* @var User $model */
        return new static(
            name: trim("{$model->first_name} {$model->last_name}"),
            email: $model->public_email,
            id: $model->id,
            avatarUrl: $model->avatar_url,
            createdAt: $model->created_at,
        );
    }

    public static function fromArray(array $data): static
    {
        return new static(
            name: trim("{$data['first_name']} {$data['last_name']}"),
            email: $data['email'] ?? null,
            id: $data['id'] ?? null,
            avatarUrl: $data['avatar_url'] ?? null,
            createdAt: $data['created_at'] ? Carbon::parse($data['created_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'email'     => $this->email,
            'avatarUrl' => $this->avatarUrl,
            'createdAt' => $this->createdAt?->toIso8601String(),
        ];
    }
}
