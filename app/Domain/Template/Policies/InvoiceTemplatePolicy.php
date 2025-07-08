<?php

namespace App\Domain\Template\Policies;

use App\Domain\Auth\Models\User;
use App\Domain\Template\Models\InvoiceTemplate;

class InvoiceTemplatePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('invoice_templates.manage');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, InvoiceTemplate $invoiceTemplate): bool
    {
        return $user->hasPermissionTo('invoice_templates.manage')
               && $this->belongsToUserTenant($user, $invoiceTemplate);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('invoice_templates.manage');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, InvoiceTemplate $invoiceTemplate): bool
    {
        return $user->hasPermissionTo('invoice_templates.manage')
               && $this->belongsToUserTenant($user, $invoiceTemplate);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, InvoiceTemplate $invoiceTemplate): bool
    {
        return $user->hasPermissionTo('invoice_templates.manage')
               && $this->belongsToUserTenant($user, $invoiceTemplate);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, InvoiceTemplate $invoiceTemplate): bool
    {
        return $user->hasPermissionTo('invoice_templates.manage')
               && $this->belongsToUserTenant($user, $invoiceTemplate);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, InvoiceTemplate $invoiceTemplate): bool
    {
        return $user->hasPermissionTo('invoice_templates.manage')
               && $this->belongsToUserTenant($user, $invoiceTemplate);
    }

    /**
     * Determine whether the user can set template as default.
     */
    public function setDefault(User $user, InvoiceTemplate $invoiceTemplate): bool
    {
        return $user->hasPermissionTo('invoice_templates.manage')
               && $this->belongsToUserTenant($user, $invoiceTemplate);
    }

    /**
     * Determine whether the user can preview templates.
     */
    public function preview(User $user): bool
    {
        return $user->hasPermissionTo('invoice_templates.manage');
    }

    /**
     * Determine whether the user can generate PDFs.
     */
    public function generatePdf(User $user): bool
    {
        return $user->hasPermissionTo('invoice_templates.manage');
    }

    /**
     * Check if template belongs to user's tenant.
     */
    private function belongsToUserTenant(User $user, InvoiceTemplate $invoiceTemplate): bool
    {
        return null === $invoiceTemplate->tenant_id || $user->tenant_id === $invoiceTemplate->tenant_id;
    }
}
