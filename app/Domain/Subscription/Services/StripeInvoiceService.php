<?php

namespace App\Domain\Subscription\Services;

/**
 * Service for managing Stripe invoices and local SubscriptionInvoice records.
 */
class StripeInvoiceService
{
    /**
     * Sync a Stripe invoice to a local SubscriptionInvoice record.
     */
    public function syncInvoice(array $stripeInvoiceData): void
    {
        // TODO: Implement syncing logic
    }

    /**
     * Generate a PDF for a given invoice (if not provided by Stripe).
     */
    public function generatePdf(string $invoiceId): string
    {
        // TODO: Implement PDF generation logic
        return '';
    }

    /**
     * Handle payment status updates for an invoice.
     */
    public function updatePaymentStatus(string $invoiceId, string $status): void
    {
        // TODO: Implement payment status update logic
    }
}
