<?php

namespace App\Domain\Expense\Traits;

/**
 * Trait that provides implementation for AllocationDimensionInterface.
 *
 * This trait can be used by all dimension models to automatically
 * implement the required interface methods.
 */
trait HasAllocationDimensionInterface
{
    /**
     * Get the unique identifier.
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get the code (optional - some models may not have codes).
     */
    public function getCode(): ?string
    {
        return $this->code ?? null;
    }

    /**
     * Get the display name.
     */
    public function getName(): ?string
    {
        return $this->name ?? null;
    }

    /**
     * Get the description (optional).
     */
    public function getDescription(): ?string
    {
        return $this->description ?? null;
    }

    /**
     * Get the tenant ID (null for global items).
     */
    public function getTenantId(): ?string
    {
        return $this->tenant_id ?? null;
    }

    /**
     * Check if the item is active.
     */
    public function getIsActive(): bool
    {
        return $this->is_active ?? true;
    }

    /**
     * Get the display name for UI purposes.
     * Falls back to name or id if display_name is not available.
     */
    public function getDisplayName(): string
    {
        // Check if model has a display_name attribute (computed property)
        if (isset($this->display_name)) {
            return $this->display_name;
        }

        // Check if model has a getDisplayNameAttribute method
        if (method_exists($this, 'getDisplayNameAttribute')) {
            return $this->getDisplayNameAttribute();
        }

        // Fall back to code + name format or just name
        if ($this->getCode() && $this->getName()) {
            return $this->getCode() . ' - ' . $this->getName();
        }

        return $this->getName() ?? $this->getId();
    }

    /**
     * Check if this is a global (tenant_id = null) item.
     */
    public function isGlobal(): bool
    {
        return null === $this->getTenantId();
    }
}
