<?php

namespace App\Domain\Template\DTOs;

use App\Domain\Common\DTOs\BaseDTO;
use App\Domain\Template\Enums\TemplateCategory;
use App\Domain\Template\Models\InvoiceTemplate;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends BaseDTO<InvoiceTemplate>
 */
final class InvoiceTemplateDTO extends BaseDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $content,
        public readonly TemplateCategory $category,
        public readonly bool $isActive,
        public readonly bool $isDefault,
        public readonly ?string $id = null,
        public readonly ?string $tenantId = null,
        public readonly ?string $userId = null,
        public readonly ?string $description = null,
        public readonly array $previewData = [],
        public readonly array $settings = [],
        public ?Carbon $createdAt = null,
        public ?Carbon $updatedAt = null,
    ) {
    }

    public static function fromModel(Model $model): static
    {
        if (!$model instanceof InvoiceTemplate) {
            throw new \InvalidArgumentException('Model must be instance of InvoiceTemplate');
        }

        return new self(
            name: $model->name,
            content: $model->content,
            category: $model->category,
            isActive: $model->is_active,
            isDefault: $model->is_default,
            id: $model->id,
            tenantId: $model->tenant_id,
            userId: $model->user_id,
            description: $model->description,
            previewData: $model->preview_data,
            settings: $model->settings,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
        );
    }

    public static function fromArray(array $data): static
    {
        return new self(
            name: $data['name'],
            content: $data['content'],
            category: TemplateCategory::from($data['category']),
            isActive: $data['is_active'] ?? true,
            isDefault: $data['is_default'] ?? false,
            id: $data['id'] ?? null,
            tenantId: $data['tenant_id'] ?? null,
            userId: $data['user_id'] ?? null,
            description: $data['description'] ?? null,
            previewData: $data['preview_data'] ?? [],
            settings: $data['settings'] ?? [],
            createdAt: isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? Carbon::parse($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'tenantId'    => $this->tenantId,
            'userId'      => $this->userId,
            'name'        => $this->name,
            'description' => $this->description,
            'content'     => $this->content,
            'category'    => $this->category->value,
            'previewData' => $this->previewData,
            'settings'    => $this->settings,
            'isActive'    => $this->isActive,
            'isDefault'   => $this->isDefault,
            'createdAt'   => $this->createdAt?->toIso8601String(),
            'updatedAt'   => $this->updatedAt?->toIso8601String(),
        ];
    }
}
