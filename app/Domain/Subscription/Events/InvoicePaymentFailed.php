<?php

namespace App\Domain\Subscription\Events;

use App\Domain\Subscription\Models\SubscriptionInvoice;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvoicePaymentFailed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public SubscriptionInvoice $invoice
    ) {
    }
}
