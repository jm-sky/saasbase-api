<?php

namespace App\Domain\Subscription\Services;

use App\Domain\Auth\Models\User;
use App\Domain\Subscription\Enums\SubscriptionInvoiceStatus;
use App\Domain\Subscription\Events\InvoicePaid;
use App\Domain\Subscription\Events\InvoicePaymentFailed;
use App\Domain\Subscription\Exceptions\StripeException;
use App\Domain\Subscription\Models\BillingCustomer;
use App\Domain\Subscription\Models\SubscriptionInvoice;
use App\Domain\Tenant\Models\Tenant;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * Service for managing Stripe invoices and local SubscriptionInvoice records.
 */
class StripeInvoiceService extends StripeService
{
    /**
     * Sync a Stripe invoice to a local SubscriptionInvoice record.
     *
     * @throws StripeException
     */
    public function syncInvoice(array $stripeInvoiceData): SubscriptionInvoice
    {
        return $this->handleStripeException(function () use ($stripeInvoiceData) {
            // Find or create local invoice record
            $invoice = SubscriptionInvoice::firstOrNew([
                'stripe_invoice_id' => $stripeInvoiceData['id'],
            ]);

            // Find associated billing customer
            $billingCustomer = BillingCustomer::where('stripe_customer_id', $stripeInvoiceData['customer'])->first();

            if (!$billingCustomer) {
                throw new StripeException('Cannot sync invoice: customer not found');
            }

            // Calculate amount due - use total if amount_due is not set
            $amountDue = $stripeInvoiceData['amount_due'] ?? $stripeInvoiceData['total'] ?? 0;

            // Update invoice data
            $invoice->fill([
                'billable_type'       => BillingCustomer::class,
                'billable_id'         => $billingCustomer->id,
                'amount_due'          => $this->unformatAmount($amountDue),
                'status'              => SubscriptionInvoiceStatus::from($stripeInvoiceData['status']),
                'hosted_invoice_url'  => $stripeInvoiceData['hosted_invoice_url'] ?? null,
                'pdf_url'             => $stripeInvoiceData['invoice_pdf'] ?? null,
                'issued_at'           => Carbon::createFromTimestamp($stripeInvoiceData['created']),
                'paid_at'             => SubscriptionInvoiceStatus::from($stripeInvoiceData['status'])->isPaid() ? Carbon::createFromTimestamp($stripeInvoiceData['created']) : null,
            ]);

            $invoice->save();

            // Generate and store PDF if not provided by Stripe
            if (!$invoice->pdf_url) {
                $pdfPath = $this->generatePdf($invoice->id);
                $invoice->update(['pdf_url' => $pdfPath]);
            }

            return $invoice;
        });
    }

    /**
     * Generate a PDF for a given invoice.
     *
     * @throws StripeException
     */
    public function generatePdf(string $invoiceId): string
    {
        return $this->handleStripeException(function () use ($invoiceId) {
            $invoice     = SubscriptionInvoice::findOrFail($invoiceId);
            /** @var BillingCustomer $customer */
            $customer    = $invoice->billingCustomer;
            /** @var User|Tenant $billable */
            $billable    = $customer->billable;
            $billingInfo = $billable->billingInfo;

            // Generate PDF using Laravel PDF package
            $pdf = Pdf::loadView('subscription.invoices.pdf', [
                'invoice'      => $invoice,
                'customer'     => $customer,
                'billingInfo'  => $billingInfo,
            ]);

            // Store PDF in storage
            $path = "invoices/{$invoice->stripe_invoice_id}.pdf";
            Storage::put($path, $pdf->output());

            return $path;
        });
    }

    /**
     * Handle payment status updates for an invoice.
     *
     * @throws StripeException
     */
    public function updatePaymentStatus(string $invoiceId, string $status): SubscriptionInvoice
    {
        return $this->handleStripeException(function () use ($invoiceId, $status) {
            // TODO: MAYBE ADD TENANT SCOPE & BYPASS HERE
            $invoice       = SubscriptionInvoice::where('stripe_invoice_id', $invoiceId)->firstOrFail();
            $invoiceStatus = SubscriptionInvoiceStatus::from($status);

            $updates = [
                'status' => $invoiceStatus,
            ];

            if ($invoiceStatus->isPaid()) {
                $updates['paid_at'] = Carbon::now();
            } elseif ($invoiceStatus->isVoid()) {
                $updates['voided_at'] = Carbon::now();
            }

            $invoice->update($updates);

            // Trigger appropriate events based on status
            if ($invoiceStatus->isPaid()) {
                event(new InvoicePaid($invoice));
            } elseif ($invoiceStatus->isFailed()) {
                event(new InvoicePaymentFailed($invoice));
            }

            return $invoice;
        });
    }

    /**
     * Get invoice details from Stripe.
     *
     * @throws StripeException
     */
    public function getStripeInvoice(string $stripeInvoiceId): array
    {
        return $this->handleStripeException(function () use ($stripeInvoiceId) {
            $stripeInvoice = $this->stripe->invoices->retrieve($stripeInvoiceId);

            return $stripeInvoice->toArray();
        });
    }

    /**
     * List invoices for a customer.
     *
     * @throws StripeException
     */
    public function listCustomerInvoices(BillingCustomer $customer, array $options = []): array
    {
        return $this->handleStripeException(function () use ($customer, $options) {
            $params = array_merge([
                'customer' => $customer->stripe_customer_id,
                'limit'    => $options['limit'] ?? 10,
            ], $options);

            $invoices = $this->stripe->invoices->all($params);

            return $invoices->toArray();
        });
    }
}
