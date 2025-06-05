<?php

namespace App\Domain\Subscription\Controllers;

use App\Domain\Subscription\Services\StripeInvoiceService;
use App\Domain\Subscription\Services\StripeSubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Event;

class StripeWebhookController
{
    public function __construct(
        protected StripeSubscriptionService $stripeSubscriptionService,
        protected StripeInvoiceService $stripeInvoiceService
    ) {
    }

    /**
     * Handle incoming Stripe webhooks.
     */
    public function __invoke(Request $request): Response
    {
        try {
            $event = $request->get('stripe_event');

            $this->handleEvent($event);
        } catch (\Exception $e) {
            Log::error('Failed to handle Stripe webhook', [
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
                'event'    => $event->type ?? 'unknown',
                'event_id' => $event->id ?? 'unknown',
            ]);

            return response('Webhook handler failed', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response('Webhook handled successfully', Response::HTTP_OK);
    }

    /**
     * Handle different types of Stripe events.
     */
    protected function handleEvent(Event $event): void
    {
        match ($event->type) {
            'customer.subscription.created' => $this->handleSubscriptionCreated($event),
            'customer.subscription.updated' => $this->handleSubscriptionUpdated($event),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($event),
            'invoice.created'               => $this->handleInvoiceCreated($event),
            'invoice.payment_succeeded'     => $this->handleInvoicePaymentSucceeded($event),
            'invoice.payment_failed'        => $this->handleInvoicePaymentFailed($event),
            default                         => Log::info('Unhandled Stripe event', [
                'type'     => $event->type,
                'event_id' => $event->id,
            ]),
        };
    }

    /**
     * Handle subscription created event.
     */
    protected function handleSubscriptionCreated(Event $event): void
    {
        $subscription = $event->data->object;
        $this->stripeSubscriptionService->syncSubscription($subscription->id);
    }

    /**
     * Handle subscription updated event.
     */
    protected function handleSubscriptionUpdated(Event $event): void
    {
        $subscription = $event->data->object;
        $this->stripeSubscriptionService->syncSubscription($subscription->id);
    }

    /**
     * Handle subscription deleted event.
     */
    protected function handleSubscriptionDeleted(Event $event): void
    {
        $subscription = $event->data->object;
        $this->stripeSubscriptionService->syncSubscription($subscription->id);
    }

    /**
     * Handle invoice created event.
     */
    protected function handleInvoiceCreated(Event $event): void
    {
        $invoice = $event->data->object;
        $this->stripeInvoiceService->syncInvoice($invoice->toArray());
    }

    /**
     * Handle invoice payment succeeded event.
     */
    protected function handleInvoicePaymentSucceeded(Event $event): void
    {
        $invoice = $event->data->object;

        // First ensure the invoice exists
        $this->stripeInvoiceService->syncInvoice($invoice->toArray());

        // Then update its status
        $this->stripeInvoiceService->updatePaymentStatus($invoice->id, 'paid');
    }

    /**
     * Handle invoice payment failed event.
     */
    protected function handleInvoicePaymentFailed(Event $event): void
    {
        $invoice = $event->data->object;

        // First ensure the invoice exists
        $this->stripeInvoiceService->syncInvoice($invoice->toArray());

        // Then update its status
        $this->stripeInvoiceService->updatePaymentStatus($invoice->id, 'failed');
    }
}
