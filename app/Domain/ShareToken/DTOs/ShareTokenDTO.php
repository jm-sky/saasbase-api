<?php

namespace App\Domain\ShareToken\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;
use App\Domain\ShareToken\Models\ShareToken;
use Carbon\Carbon;

/**
 * @property string  $id
 * @property string  $token
 * @property string  $shareableType
 * @property string  $shareableId
 * @property bool    $onlyForAuthenticated
 * @property ?Carbon $expiresAt
 * @property ?Carbon $lastUsedAt
 * @property int     $usageCount
 * @property ?int    $maxUsage
 * @property Carbon  $createdAt
 * @property Carbon  $updatedAt
 */
final class ShareTokenDTO extends BaseDataDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $token,
        public readonly string $shareableType,
        public readonly string $shareableId,
        public readonly bool $onlyForAuthenticated,
        public readonly ?Carbon $expiresAt,
        public readonly ?Carbon $lastUsedAt,
        public readonly int $usageCount,
        public readonly ?int $maxUsage,
        public readonly Carbon $createdAt,
        public readonly Carbon $updatedAt,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            id: $data['id'],
            token: $data['token'],
            shareableType: $data['shareableType'],
            shareableId: $data['shareableId'],
            onlyForAuthenticated: $data['onlyForAuthenticated'],
            expiresAt: $data['expiresAt'],
            lastUsedAt: $data['lastUsedAt'],
            usageCount: $data['usageCount'],
            maxUsage: $data['maxUsage'],
            createdAt: $data['createdAt'],
            updatedAt: $data['updatedAt'],
        );
    }

    public static function fromModel(ShareToken $model): static
    {
        return new self(
            id: $model->id,
            token: $model->token,
            shareableType: $model->shareable_type,
            shareableId: $model->shareable_id,
            onlyForAuthenticated: $model->only_for_authenticated,
            expiresAt: $model->expires_at,
            lastUsedAt: $model->last_used_at,
            usageCount: $model->usage_count,
            maxUsage: $model->max_usage,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
        );
    }

    public function toArray(): array
    {
        return [
            'id'                   => $this->id,
            'token'                => $this->token,
            'shareableType'        => $this->shareableType,
            'shareableId'          => $this->shareableId,
            'onlyForAuthenticated' => $this->onlyForAuthenticated,
            'expiresAt'            => $this->expiresAt?->toIso8601String(),
            'lastUsedAt'           => $this->lastUsedAt?->toIso8601String(),
            'usageCount'           => $this->usageCount,
            'maxUsage'             => $this->maxUsage,
            'createdAt'            => $this->createdAt->toIso8601String(),
            'updatedAt'            => $this->updatedAt->toIso8601String(),
        ];
    }
}
