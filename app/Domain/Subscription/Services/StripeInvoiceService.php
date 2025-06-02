<?php

namespace App\Domain\Subscription\Services;

use App\Domain\Subscription\Enums\SubscriptionInvoiceStatus;
use App\Domain\Subscription\Events\InvoicePaid;
use App\Domain\Subscription\Events\InvoicePaymentFailed;
use App\Domain\Subscription\Exceptions\StripeException;
use App\Domain\Subscription\Models\BillingCustomer;
use App\Domain\Subscription\Models\SubscriptionInvoice;
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

            // Update invoice data
            $invoice->fill([
                'billing_customer_id' => $billingCustomer->id,
                'amount'              => $this->unformatAmount($stripeInvoiceData['amount_due']),
                'currency'            => $stripeInvoiceData['currency'],
                'status'              => SubscriptionInvoiceStatus::from($stripeInvoiceData['status']),
                'due_date'            => $stripeInvoiceData['due_date'] ? Carbon::createFromTimestamp($stripeInvoiceData['due_date']) : null,
                'paid_at'             => SubscriptionInvoiceStatus::from($stripeInvoiceData['status'])->isPaid() ? Carbon::createFromTimestamp($stripeInvoiceData['created']) : null,
                'hosted_url'          => $stripeInvoiceData['hosted_invoice_url'],
                'invoice_pdf'         => $stripeInvoiceData['invoice_pdf'],
                'number'              => $stripeInvoiceData['number'],
                'description'         => $stripeInvoiceData['description'],
                'metadata'            => $stripeInvoiceData['metadata'] ?? [],
            ]);

            $invoice->save();

            // Generate and store PDF if not provided by Stripe
            if (!$invoice->invoice_pdf) {
                $pdfPath = $this->generatePdf($invoice->id);
                $invoice->update(['invoice_pdf' => $pdfPath]);
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
            $customer    = $invoice->billingCustomer;
            $billingInfo = $customer->billingInfo;

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
            $invoice       = SubscriptionInvoice::findOrFail($invoiceId);
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
