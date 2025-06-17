<?php

namespace App\Domain\Tenant\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;

final class TenantQuotaItemDTO extends BaseDataDTO
{
    public function __construct(
        public readonly float $used,
        public readonly float $total,
        public readonly ?string $unit = null,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            used: $data['used'],
            total: $data['total'],
            unit: $data['unit'],
        );
    }

    public function toArray(): array
    {
        return [
            'used'  => $this->used,
            'total' => $this->total,
            'unit'  => $this->unit,
        ];
    }
}
