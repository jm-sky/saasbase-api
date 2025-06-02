<?php

namespace App\Domain\Subscription\Exceptions;

class StripeException extends \Exception
{
    protected ?string $stripeCode;

    public function __construct(string $message, ?string $stripeCode = null, ?\Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->stripeCode = $stripeCode;
    }

    public function getStripeCode(): ?string
    {
        return $this->stripeCode;
    }
}
