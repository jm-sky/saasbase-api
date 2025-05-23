<?php

namespace App\Domain\Tenant\DTOs;

use App\Domain\Common\DTOs\BaseDTO;
use App\Domain\Common\DTOs\MediaDTO;
use App\Domain\Tenant\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends BaseDTO<Tenant>
 *
 * @property string    $name
 * @property string    $slug
 * @property ?string   $id          UUID
 * @property ?string   $country
 * @property ?string   $taxId
 * @property ?string   $vatId
 * @property ?string   $regon
 * @property ?string   $email
 * @property ?string   $phone
 * @property ?string   $website
 * @property ?string   $description
 * @property ?Carbon   $createdAt   Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon   $updatedAt   Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon   $deletedAt   Internally Carbon, accepts/serializes ISO 8601
 * @property ?string   $logoUrl
 * @property ?MediaDTO $logo
 */
class TenantDTO extends BaseDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly ?string $id = null,
        public readonly ?string $country = null,
        public readonly ?string $taxId = null,
        public readonly ?string $vatId = null,
        public readonly ?string $regon = null,
        public readonly ?string $email = null,
        public readonly ?string $phone = null,
        public readonly ?string $website = null,
        public readonly ?string $description = null,
        public readonly ?string $logoUrl = null,
        public ?Carbon $createdAt = null,
        public ?Carbon $updatedAt = null,
        public ?Carbon $deletedAt = null,
        public readonly ?MediaDTO $logo = null,
    ) {
    }

    public static function fromModel(Model $model): static
    {
        $logoMedia = $model->getFirstMedia('logo');

        /* @var Tenant $model */
        return new static(
            id: $model->id,
            name: $model->name,
            slug: $model->slug,
            taxId: $model->tax_id,
            vatId: $model->vat_id,
            regon: $model->regon,
            email: $model->email,
            phone: $model->phone,
            website: $model->website,
            country: $model->country,
            description: $model->description,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
            deletedAt: $model->deleted_at,
            logo: $logoMedia ? MediaDTO::fromModel($logoMedia, parent: $model) : null,
            logoUrl: $logoMedia ? $logoMedia->getUrl() : null,
        );
    }

    public static function fromArray(array $data): static
    {
        return new static(
            name: $data['name'],
            slug: $data['slug'],
            id: $data['id'] ?? null,
            taxId: $data['tax_id'] ?? null,
            vatId: $data['vat_id'] ?? null,
            regon: $data['regon'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            website: $data['website'] ?? null,
            country: $data['country'] ?? null,
            description: $data['description'] ?? null,
            createdAt: isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? Carbon::parse($data['updated_at']) : null,
            deletedAt: isset($data['deleted_at']) ? Carbon::parse($data['deleted_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'taxId'       => $this->taxId,
            'vatId'       => $this->vatId,
            'regon'       => $this->regon,
            'email'       => $this->email,
            'phone'       => $this->phone,
            'website'     => $this->website,
            'country'     => $this->country,
            'description' => $this->description,
            'createdAt'   => $this->createdAt?->toIso8601String(),
            'updatedAt'   => $this->updatedAt?->toIso8601String(),
            'deletedAt'   => $this->deletedAt?->toIso8601String(),
            'logoUrl'     => $this->logoUrl,
            'logo'        => $this->logo?->toArray(),
        ];
    }
}
