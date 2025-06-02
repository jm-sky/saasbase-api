<?php

namespace App\Domain\Subscription\Events;

use App\Domain\Subscription\Models\AddonPurchase;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AddonPurchased
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public AddonPurchase $purchase
    ) {
    }
}
