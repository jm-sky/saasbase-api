<?php

namespace App\Domain\Financial\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;
use Carbon\Carbon;

final class GtuCodeDTO extends BaseDataDTO
{
    public function __construct(
        public string $id,
        public string $code,
        public string $name,
        public string $description,
        public ?float $amountThresholdPln = null,
        public ?array $applicableConditions = null,
        public bool $isActive = true,
        public ?Carbon $effectiveFrom = null,
        public ?Carbon $effectiveTo = null,
        public ?Carbon $createdAt = null,
        public ?Carbon $updatedAt = null,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            id: $data['id'],
            code: $data['code'],
            name: $data['name'],
            description: $data['description'],
            amountThresholdPln: $data['amount_threshold_pln'] ?? null,
            applicableConditions: $data['applicable_conditions'] ?? null,
            isActive: $data['is_active'] ?? true,
            effectiveFrom: isset($data['effective_from']) ? Carbon::parse($data['effective_from']) : null,
            effectiveTo: isset($data['effective_to']) ? Carbon::parse($data['effective_to']) : null,
            createdAt: isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? Carbon::parse($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'                    => $this->id,
            'code'                  => $this->code,
            'name'                  => $this->name,
            'description'           => $this->description,
            'amount_threshold_pln'  => $this->amountThresholdPln,
            'applicable_conditions' => $this->applicableConditions,
            'is_active'             => $this->isActive,
            'effective_from'        => $this->effectiveFrom?->toIso8601String(),
            'effective_to'          => $this->effectiveTo?->toIso8601String(),
            'created_at'            => $this->createdAt?->toIso8601String(),
            'updated_at'            => $this->updatedAt?->toIso8601String(),
        ];
    }
}
