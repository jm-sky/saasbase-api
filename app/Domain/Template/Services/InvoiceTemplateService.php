<?php

namespace App\Domain\Template\Services;

use App\Domain\Template\DTOs\InvoiceTemplateDTO;
use App\Domain\Template\Enums\TemplateCategory;
use App\Domain\Template\Exceptions\TemplateNotFoundException;
use App\Domain\Template\Models\InvoiceTemplate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class InvoiceTemplateService
{
    public function __construct(
        private TemplatingService $templatingService
    ) {
    }

    /**
     * Get all templates for a tenant.
     */
    public function getAllForTenant(string $tenantId): Collection
    {
        return InvoiceTemplate::query()
            ->where('tenant_id', $tenantId)
            ->active()
            ->orderBy('name')
            ->get()
        ;
    }

    /**
     * Get paginated templates for a tenant.
     */
    public function getPaginatedForTenant(string $tenantId, int $perPage = 15): LengthAwarePaginator
    {
        return InvoiceTemplate::query()
            ->where('tenant_id', $tenantId)
            ->active()
            ->orderBy('name')
            ->paginate($perPage)
        ;
    }

    /**
     * Get templates by category.
     */
    public function getByCategory(string $tenantId, TemplateCategory $category): Collection
    {
        return InvoiceTemplate::query()
            ->where('tenant_id', $tenantId)
            ->byCategory($category)
            ->active()
            ->orderBy('name')
            ->get()
        ;
    }

    /**
     * Find template by ID.
     */
    public function findById(string $id): InvoiceTemplate
    {
        $template = InvoiceTemplate::find($id);

        if (!$template) {
            throw new TemplateNotFoundException("Template with ID {$id} not found");
        }

        return $template;
    }

    /**
     * Get default template for category.
     */
    public function getDefaultForCategory(string $tenantId, TemplateCategory $category): ?InvoiceTemplate
    {
        return InvoiceTemplate::query()
            ->where('tenant_id', $tenantId)
            ->byCategory($category)
            ->default()
            ->active()
            ->first()
        ;
    }

    /**
     * Create a new template.
     */
    public function create(InvoiceTemplateDTO $dto): InvoiceTemplate
    {
        // Validate template syntax
        if (!$this->templatingService->validate($dto->content)) {
            $errors = $this->templatingService->getValidationErrors($dto->content);

            throw new \InvalidArgumentException("Invalid template syntax: {$errors}");
        }

        // If this is set as default, unset other defaults in the same category
        if ($dto->isDefault) {
            $this->unsetDefaultForCategory($dto->tenantId, $dto->category);
        }

        return InvoiceTemplate::create($dto->toDbArray());
    }

    /**
     * Update an existing template.
     */
    public function update(string $id, InvoiceTemplateDTO $dto): InvoiceTemplate
    {
        $template = $this->findById($id);

        // Validate template syntax
        if (!$this->templatingService->validate($dto->content)) {
            $errors = $this->templatingService->getValidationErrors($dto->content);

            throw new \InvalidArgumentException("Invalid template syntax: {$errors}");
        }

        // If this is set as default, unset other defaults in the same category
        if ($dto->isDefault && (!$template->is_default || $template->category !== $dto->category)) {
            $this->unsetDefaultForCategory($dto->tenantId, $dto->category);
        }

        $template->update($dto->toDbArray());

        return $template->fresh();
    }

    /**
     * Delete a template.
     */
    public function delete(string $id): bool
    {
        $template = $this->findById($id);

        return $template->delete();
    }

    /**
     * Set template as default for its category.
     */
    public function setAsDefault(string $id): InvoiceTemplate
    {
        $template = $this->findById($id);

        // Unset other defaults in the same category
        $this->unsetDefaultForCategory($template->tenant_id, $template->category);

        $template->update(['is_default' => true]);

        return $template->fresh();
    }

    /**
     * Validate template content.
     */
    public function validateTemplate(string $content): array
    {
        $isValid = $this->templatingService->validate($content);
        $errors  = $isValid ? null : $this->templatingService->getValidationErrors($content);

        return [
            'isValid' => $isValid,
            'errors'  => $errors,
        ];
    }

    /**
     * Preview template with sample data.
     */
    public function preview(string $content, array $data): string
    {
        return $this->templatingService->render($content, $data);
    }

    /**
     * Unset default flag for all templates in category.
     */
    private function unsetDefaultForCategory(string $tenantId, TemplateCategory $category): void
    {
        InvoiceTemplate::query()
            ->where('tenant_id', $tenantId)
            ->byCategory($category)
            ->where('is_default', true)
            ->update(['is_default' => false]);
    }
}
