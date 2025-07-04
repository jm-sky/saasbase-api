<?php

namespace App\Domain\Tenant\Builders;

use Illuminate\Database\Eloquent\Builder;

/**
 * Custom query builder for OrgUnitUser with typed scope methods.
 *
 * Note: Due to PHPStan limitations with HasMany relations and whereHas callbacks,
 * this approach doesn't fully solve the typing issue. The best practical solution
 * is to use @method annotations and live with the fact that some scopes need
 * manual where conditions in certain contexts.
 */
class OrgUnitUserBuilder extends Builder
{
    /**
     * Scope to only active organization unit users.
     */
    public function active(): self
    {
        return $this->where('is_active', true)
            ->where('valid_from', '<=', now())
            ->where(function ($query) {
                $query->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', now())
                ;
            })
        ;
    }

    /**
     * Scope to only primary organization unit users.
     */
    public function primary(): self
    {
        return $this->where('is_primary', true);
    }

    public function activePrimary(): self
    {
        return $this->active()->primary();
    }

    /**
     * Scope by workflow role level.
     */
    public function withWorkflowRole($roleLevel): self
    {
        return $this->where('workflow_role_level', $roleLevel);
    }

    /**
     * Scope to memberships with positions.
     */
    public function withPosition(): self
    {
        return $this->whereNotNull('position_id');
    }

    /**
     * Scope to memberships without positions.
     */
    public function withoutPosition(): self
    {
        return $this->whereNull('position_id');
    }
}
