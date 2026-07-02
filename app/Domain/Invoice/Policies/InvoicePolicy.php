<?php

namespace App\Domain\Invoice\Policies;

use App\Domain\Auth\Models\User;
use App\Domain\Invoice\Models\Invoice;
use App\Domain\Rights\Enums\RoleName;
use App\Domain\Rights\Support\TenantScopedRoles;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoicePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return null !== $user->getTenantId();
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->getTenantId() === $invoice->tenant_id;
    }

    public function create(User $user): bool
    {
        return null !== $user->getTenantId();
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->getTenantId() === $invoice->tenant_id;
    }

    /**
     * Deleting a financial document is destructive — restricted to
     * tenant Owner/Admin, unlike create/update which any member needs
     * for day-to-day invoicing.
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        if ($user->getTenantId() !== $invoice->tenant_id) {
            return false;
        }

        return TenantScopedRoles::userHasAnyRole($user, $invoice->tenant_id, [RoleName::Owner->value, RoleName::Admin->value]);
    }
}
