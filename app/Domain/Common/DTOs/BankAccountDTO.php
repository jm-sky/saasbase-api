<?php

namespace App\Domain\Common\DTOs;

use App\Domain\Common\Models\BankAccount;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends BaseDTO<BankAccount>
 *
 * @property string  $id           UUID
 * @property ?string $tenantId    UUID
 * @property string  $bankableId   UUID
 * @property string  $bankableType
 * @property string  $iban
 * @property ?string $swift
 * @property bool    $isDefault
 * @property ?string $currency
 * @property ?string $bankName
 * @property ?string $description
 * @property Carbon  $createdAt    Internally Carbon, accepts/serializes ISO 8601
 * @property Carbon  $updatedAt    Internally Carbon, accepts/serializes ISO 8601
 */
class BankAccountDTO extends BaseDTO
{
    public function __construct(
        public readonly string $bankableId,
        public readonly string $bankableType,
        public readonly string $iban,
        public readonly ?string $tenantId = null,
        public readonly ?string $swift = null,
        public readonly ?string $bankName = null,
        public readonly bool $isDefault = false,
        public readonly ?string $currency = null,
        public readonly ?string $description = null,
        public readonly ?string $id = null,
        public ?Carbon $createdAt = null,
        public ?Carbon $updatedAt = null,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            tenantId: $data['tenant_id'] ?? null,
            bankableId: $data['bankable_id'],
            bankableType: $data['bankable_type'],
            iban: $data['iban'],
            swift: $data['swift'] ?? null,
            bankName: $data['bank_name'] ?? null,
            isDefault: $data['is_default'] ?? false,
            currency: $data['currency'] ?? null,
            description: $data['description'] ?? null,
            id: $data['id'] ?? null,
            createdAt: isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? Carbon::parse($data['updated_at']) : null,
        );
    }

    public static function fromModel(Model $model): static
    {
        if (!$model instanceof BankAccount) {
            throw new \InvalidArgumentException('Model must be instance of BankAccount');
        }

        return new self(
            tenantId: $model->tenant_id ?? null,
            bankableId: $model->bankable_id,
            bankableType: $model->bankable_type,
            bankName: $model->bank_name ?? null,
            iban: $model->iban,
            swift: $model->swift ?? null,
            isDefault: $model->is_default,
            currency: $model->currency ?? null,
            description: $model->description ?? null,
            id: $model->id,
            createdAt: $model->created_at ? Carbon::parse($model->created_at) : null,
            updatedAt: $model->updated_at ? Carbon::parse($model->updated_at) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'            => $this->id,
            'tenantId'      => $this->tenantId,
            'bankableId'    => $this->bankableId,
            'bankableType'  => $this->bankableType,
            'iban'          => $this->iban,
            'swift'         => $this->swift,
            'bankName'      => $this->bankName,
            'isDefault'     => $this->isDefault,
            'currency'      => $this->currency,
            'description'   => $this->description,
            'createdAt'     => $this->createdAt?->toIso8601String(),
            'updatedAt'     => $this->updatedAt?->toIso8601String(),
        ];
    }
}
