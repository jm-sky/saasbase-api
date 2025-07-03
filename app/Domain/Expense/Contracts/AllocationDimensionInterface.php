<?php

namespace App\Domain\Expense\Contracts;

/**
 * Interface for all allocation dimension models.
 *
 * All dimension models (TransactionType, CostType, Location, etc.) should implement this interface
 * to ensure they have the required properties for allocation dimension functionality.
 */
interface AllocationDimensionInterface
{
    /**
     * Get the unique identifier.
     */
    public function getId(): string;

    /**
     * Get the code (optional - some models may not have codes).
     */
    public function getCode(): ?string;

    /**
     * Get the display name.
     */
    public function getName(): ?string;

    /**
     * Get the description (optional).
     */
    public function getDescription(): ?string;

    /**
     * Get the tenant ID (null for global items).
     */
    public function getTenantId(): ?string;

    /**
     * Check if the item is active.
     */
    public function getIsActive(): bool;

    /**
     * Get the display name for UI purposes.
     * Should fall back to name or id if display_name is not available.
     */
    public function getDisplayName(): string;

    /**
     * Check if this is a global (tenant_id = null) item.
     */
    public function isGlobal(): bool;
}
