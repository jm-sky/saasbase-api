<?php

namespace App\Domain\Subscription\Events;

use App\Domain\Subscription\Models\Subscription;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubscriptionCancelled
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Subscription $subscription
    ) {
    }
}
