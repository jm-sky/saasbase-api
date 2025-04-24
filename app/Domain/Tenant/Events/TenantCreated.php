<?php

namespace App\Domain\Tenant\Events;

use App\Domain\Tenant\Models\Tenant;
use Illuminate\Queue\SerializesModels;

class TenantCreated
{
    use SerializesModels;

    public Tenant $tenant;

    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }
}
