<?php

namespace App\Domain\Tenant\DTOs;

use App\Domain\Common\DTOs\BaseDTO;
use App\Domain\Tenant\Models\TenantInvitation;
use App\Domain\Users\DTOs\UserPreviewDTO;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends BaseDTO<TenantInvitation>
 *
 * @property string           $id
 * @property string           $tenantId
 * @property string           $inviterId
 * @property string           $email
 * @property string           $role
 * @property string           $token
 * @property string           $status
 * @property ?Carbon          $acceptedAt
 * @property Carbon           $expiresAt
 * @property ?Carbon          $createdAt
 * @property ?Carbon          $updatedAt
 * @property TenantPreviewDTO $tenant
 * @property UserPreviewDTO   $inviter
 * @property ?UserPreviewDTO  $invitedUser
 */
class TenantInvitationDTO extends BaseDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $tenantId,
        public readonly string $inviterId,
        public readonly string $email,
        public readonly string $role,
        public readonly string $token,
        public readonly string $status,
        public readonly ?Carbon $acceptedAt = null,
        public readonly Carbon $expiresAt,
        public readonly ?Carbon $createdAt = null,
        public readonly ?Carbon $updatedAt = null,
        public readonly TenantPreviewDTO $tenant,
        public readonly UserPreviewDTO $inviter,
        public readonly ?UserPreviewDTO $invitedUser = null,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new static(
            id: $data['id'],
            tenantId: $data['tenantId'],
            inviterId: $data['inviterId'],
            email: $data['email'],
            role: $data['role'],
            token: $data['token'],
            status: $data['status'],
            acceptedAt: $data['acceptedAt'],
            expiresAt: $data['expiresAt'],
            createdAt: $data['createdAt'],
            updatedAt: $data['updatedAt'],
            tenant: TenantPreviewDTO::fromArray($data['tenant']),
            inviter: UserPreviewDTO::fromArray($data['inviter']),
            invitedUser: $data['invitedUser'] ? UserPreviewDTO::fromArray($data['invitedUser']) : null,
        );
    }

    public static function fromModel(Model $model): static
    {
        /* @var TenantInvitation $model */
        return new static(
            id: $model->id,
            tenantId: $model->tenant_id,
            inviterId: $model->inviter_id,
            email: $model->email,
            role: $model->role,
            token: $model->token,
            status: $model->status,
            acceptedAt: $model->accepted_at,
            expiresAt: $model->expires_at,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
            tenant: TenantPreviewDTO::fromModel($model->tenant),
            inviter: UserPreviewDTO::fromModel($model->inviter),
            invitedUser: $model->invitedUser ? UserPreviewDTO::fromModel($model->invitedUser) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'tenantId'    => $this->tenantId,
            'inviterId'   => $this->inviterId,
            'email'       => $this->email,
            'role'        => $this->role,
            'token'       => $this->token,
            'status'      => $this->status,
            'tenant'      => $this->tenant->toArray(),
            'inviter'     => $this->inviter->toArray(),
            'invitedUser' => $this->invitedUser?->toArray(),
            'acceptedAt'  => $this->acceptedAt?->toIso8601String(),
            'expiresAt'   => $this->expiresAt?->toIso8601String(),
            'createdAt'   => $this->createdAt?->toIso8601String(),
            'updatedAt'   => $this->updatedAt?->toIso8601String(),
        ];
    }
}
