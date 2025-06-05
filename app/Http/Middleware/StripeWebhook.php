<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhook
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, \Closure $next)
    {
        // Skip signature verification in testing environment
        if (app()->environment('testing')) {
            return $next($request);
        }

        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        if (!$sigHeader) {
            return response('No signature provided', 400);
        }

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                config('stripe.webhook_secret')
            );

            // Store the verified event in the request for the controller to use
            $request->merge(['stripe_event' => $event]);

            return $next($request);
        } catch (SignatureVerificationException $e) {
            Log::error('Stripe webhook signature verification failed', [
                'error'               => $e->getMessage(),
                'signature'           => $sigHeader,
                'webhook_secret'      => config('stripe.webhook_secret') ? 'set' : 'not set',
                'signature_timestamp' => explode(',', $sigHeader)[0] ?? 'unknown',
            ]);

            return response('Invalid signature', 400);
        }
    }
}
