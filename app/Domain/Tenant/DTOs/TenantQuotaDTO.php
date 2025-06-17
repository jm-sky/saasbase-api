<?php

namespace App\Domain\Tenant\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;

final class TenantQuotaDTO extends BaseDataDTO
{
    public function __construct(
        public readonly TenantQuotaItemDTO $storage,
        public readonly TenantQuotaItemDTO $users,
        public readonly TenantQuotaItemDTO $apiCalls,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            storage: TenantQuotaItemDTO::fromArray($data['storage']),
            users: TenantQuotaItemDTO::fromArray($data['users']),
            apiCalls: TenantQuotaItemDTO::fromArray($data['apiCalls']),
        );
    }

    public function toArray(): array
    {
        return [
            'storage'  => $this->storage->toArray(),
            'users'    => $this->users->toArray(),
            'apiCalls' => $this->apiCalls->toArray(),
        ];
    }
}
