<?php

namespace App\Domain\Contractors\DTOs;

use App\Domain\Common\DTOs\BaseDTO;
use App\Domain\Common\DTOs\MediaDTO;
use App\Domain\Contractors\Models\Contractor;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends BaseDTO<Contractor>
 *
 * @property ?string       $id          UUID
 * @property string        $tenantId    UUID
 * @property string        $name
 * @property string        $email
 * @property ?string       $phone
 * @property ?string       $website
 * @property ?string       $country
 * @property ?string       $taxId
 * @property ?string       $description
 * @property ?bool         $isActive
 * @property ?bool         $isBuyer
 * @property ?bool         $isSupplier
 * @property ?string       $logoUrl
 * @property ?Carbon       $createdAt   Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon       $updatedAt   Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon       $deletedAt   Internally Carbon, accepts/serializes ISO 8601
 * @property string[]|null $tags
 * @property ?MediaDTO     $logo
 */
class ContractorDTO extends BaseDTO
{
    public function __construct(
        public readonly string $tenantId,
        public readonly string $name,
        public readonly ?string $id = null,
        public readonly ?string $email = null,
        public readonly ?string $phone = null,
        public readonly ?string $website = null,
        public readonly ?string $country = null,
        public readonly ?string $taxId = null,
        public readonly ?string $description = null,
        public readonly ?bool $isActive = null,
        public readonly ?bool $isBuyer = null,
        public readonly ?bool $isSupplier = null,
        public readonly ?string $logoUrl = null,
        public ?Carbon $createdAt = null,
        public ?Carbon $updatedAt = null,
        public ?Carbon $deletedAt = null,
        public readonly ?MediaDTO $logo = null,
        public readonly ?array $tags = null,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            tenantId: $data['tenant_id'],
            name: $data['name'],
            id: $data['id'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            website: $data['website'] ?? null,
            country: $data['country'] ?? null,
            taxId: $data['tax_id'] ?? null,
            description: $data['description'] ?? null,
            isActive: $data['is_active'] ?? null,
            isBuyer: $data['is_buyer'] ?? null,
            isSupplier: $data['is_supplier'] ?? null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
            deletedAt: $data['deleted_at'] ?? null,
            logo: null,
            tags: $data['tags'] ?? [],
        );
    }

    public static function fromModel(Model $model): static
    {
        if (!$model instanceof Contractor) {
            throw new \InvalidArgumentException('Model must be instance of Contractor');
        }

        $logoMedia = $model->getFirstMedia('logo');

        return new self(
            id: $model->id,
            tenantId: $model->tenant_id,
            name: $model->name,
            email: $model->email,
            phone: $model->phone,
            website: $model->website,
            country: $model->country,
            taxId: $model->tax_id,
            description: $model->description,
            isActive: $model->is_active,
            isBuyer: $model->is_buyer,
            isSupplier: $model->is_supplier,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
            deletedAt: $model->deleted_at,
            logoUrl: $logoMedia ? $model->getMediaUrl('logo', $logoMedia->file_name) : null,
            logo: $logoMedia ? MediaDTO::fromModel($logoMedia, parent: $model) : null,
            tags: method_exists($model, 'getTagNames') ? $model->getTagNames() : [],
        );
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'tenantId'    => $this->tenantId,
            'name'        => $this->name,
            'email'       => $this->email,
            'phone'       => $this->phone,
            'website'     => $this->website,
            'country'     => $this->country,
            'taxId'       => $this->taxId,
            'description' => $this->description,
            'isActive'    => $this->isActive,
            'isBuyer'     => $this->isBuyer,
            'isSupplier'  => $this->isSupplier,
            'createdAt'   => $this->createdAt?->toIso8601String(),
            'updatedAt'   => $this->updatedAt?->toIso8601String(),
            'deletedAt'   => $this->deletedAt?->toIso8601String(),
            'logoUrl'     => $this->logoUrl,
            'logo'        => $this->logo?->toArray(),
            'tags'        => $this->tags ?? [],
        ];
    }
}
