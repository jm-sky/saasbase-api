<?php

namespace App\Domain\Common\DTOs;

use App\Domain\Common\Enums\AddressType;
use App\Domain\Common\Models\Address;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends BaseDTO<Address>
 *
 * @property ?string     $id
 * @property ?string     $tenantId
 * @property string      $country
 * @property ?string     $postalCode
 * @property string      $city
 * @property ?string     $street
 * @property ?string     $building
 * @property ?string     $flat
 * @property ?string     $description
 * @property AddressType $type
 * @property bool        $isDefault
 * @property ?string     $addressableId
 * @property ?string     $addressableType
 * @property ?Carbon     $createdAt       Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon     $updatedAt       Internally Carbon, accepts/serializes ISO 8601
 */
final class AddressDTO extends BaseDTO
{
    public function __construct(
        public string $country,
        public string $city,
        public AddressType $type,
        public bool $isDefault,
        public ?string $addressableId = null,
        public ?string $addressableType = null,
        public ?string $id = null,
        public ?string $tenantId = null,
        public ?string $postalCode = null,
        public ?string $street = null,
        public ?string $building = null,
        public ?string $flat = null,
        public ?string $description = null,
        public ?Carbon $createdAt = null,
        public ?Carbon $updatedAt = null,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            country: $data['country'],
            city: $data['city'],
            type: AddressType::from($data['type']),
            isDefault: $data['is_default'],
            addressableId: $data['addressable_id'] ?? null,
            addressableType: $data['addressable_type'] ?? null,
            id: $data['id'] ?? null,
            tenantId: $data['tenant_id'] ?? null,
            postalCode: $data['postal_code'] ?? null,
            street: $data['street'] ?? null,
            building: $data['building'] ?? null,
            flat: $data['flat'] ?? null,
            description: $data['description'] ?? null,
            createdAt: isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? Carbon::parse($data['updated_at']) : null,
        );
    }

    public static function fromModel(Model $model): static
    {
        if (!$model instanceof Address) {
            throw new \InvalidArgumentException('Model must be instance of Address');
        }

        return new self(
            country: $model->country,
            city: $model->city,
            type: $model->type,
            isDefault: $model->is_default,
            addressableId: $model->addressable_id,
            addressableType: $model->addressable_type,
            id: $model->id,
            tenantId: $model->tenant_id,
            postalCode: $model->postal_code,
            street: $model->street,
            building: $model->building,
            flat: $model->flat,
            description: $model->description,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
        );
    }

    public function toArray(): array
    {
        return [
            'id'              => $this->id,
            'tenantId'        => $this->tenantId,
            'country'         => $this->country,
            'postalCode'      => $this->postalCode,
            'city'            => $this->city,
            'street'          => $this->street,
            'building'        => $this->building,
            'flat'            => $this->flat,
            'description'     => $this->description,
            'type'            => $this->type->value,
            'isDefault'       => $this->isDefault,
            'addressableId'   => $this->addressableId,
            'addressableType' => $this->addressableType,
            'createdAt'       => $this->createdAt?->toIso8601String(),
            'updatedAt'       => $this->updatedAt?->toIso8601String(),
        ];
    }
}
