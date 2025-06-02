<?php

namespace App\Domain\Subscription\Services;

use App\Domain\Subscription\Exceptions\StripeException;
use Stripe\StripeClient;

abstract class StripeService
{
    protected StripeClient $stripe;

    public function __construct(StripeClient $stripe)
    {
        $this->stripe = $stripe;
    }

    /**
     * Handle Stripe API exceptions and convert them to domain exceptions.
     *
     * @template T
     *
     * @param callable(): T $callback
     *
     * @return T
     *
     * @throws StripeException
     */
    protected function handleStripeException(callable $callback)
    {
        try {
            return $callback();
        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new StripeException(message: $e->getMessage(), stripeCode: $e->getStripeCode(), previous: $e);
        }
    }

    /**
     * Format amount for Stripe (convert to cents).
     */
    protected function formatAmount(float $amount): int
    {
        return (int) ($amount * 100);
    }

    /**
     * Format amount from Stripe (convert from cents).
     */
    protected function unformatAmount(int $amount): float
    {
        return $amount / 100;
    }
}
