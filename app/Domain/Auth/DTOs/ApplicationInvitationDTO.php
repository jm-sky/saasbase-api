<?php

namespace App\Domain\Tenant\DTOs;

use App\Domain\Auth\Models\ApplicationInvitation;
use App\Domain\Common\DTOs\BaseDTO;
use App\Domain\Users\DTOs\UserPreviewDTO;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends BaseDTO<ApplicationInvitation>
 *
 * @property string         $id
 * @property string         $inviterId
 * @property string         $email
 * @property string         $role
 * @property string         $token
 * @property string         $status
 * @property ?Carbon        $acceptedAt
 * @property Carbon         $expiresAt
 * @property ?Carbon        $createdAt
 * @property ?Carbon        $updatedAt
 * @property UserPreviewDTO $inviter
 */
class ApplicationInvitationDTO extends BaseDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $inviterId,
        public readonly string $email,
        public readonly string $role,
        public readonly string $token,
        public readonly string $status,
        public readonly ?Carbon $acceptedAt = null,
        public readonly Carbon $expiresAt,
        public readonly ?Carbon $createdAt = null,
        public readonly ?Carbon $updatedAt = null,
        public readonly UserPreviewDTO $inviter,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new static(
            id: $data['id'],
            inviterId: $data['inviterId'],
            email: $data['email'],
            role: $data['role'],
            token: $data['token'],
            status: $data['status'],
            acceptedAt: $data['acceptedAt'],
            expiresAt: $data['expiresAt'],
            createdAt: $data['createdAt'],
            updatedAt: $data['updatedAt'],
            inviter: UserPreviewDTO::fromArray($data['inviter']),
        );
    }

    public static function fromModel(Model $model): static
    {
        /* @var TenantInvitation $model */
        return new static(
            id: $model->id,
            inviterId: $model->inviter_id,
            email: $model->email,
            role: $model->role,
            token: $model->token,
            status: $model->status,
            acceptedAt: $model->accepted_at,
            expiresAt: $model->expires_at,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
            inviter: UserPreviewDTO::fromModel($model->inviter),
        );
    }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'inviterId'  => $this->inviterId,
            'email'      => $this->email,
            'role'       => $this->role,
            'token'      => $this->token,
            'status'     => $this->status,
            'inviter'    => $this->inviter->toArray(),
            'acceptedAt' => $this->acceptedAt?->toIso8601String(),
            'expiresAt'  => $this->expiresAt?->toIso8601String(),
            'createdAt'  => $this->createdAt?->toIso8601String(),
            'updatedAt'  => $this->updatedAt?->toIso8601String(),
        ];
    }
}
