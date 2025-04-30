<?php

namespace App\Domain\Exchanges\Policies;

use App\Domain\Auth\Models\User;
use App\Domain\Exchanges\Models\Exchange;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExchangePolicy
{
    use HandlesAuthorization;

    public function view(User $user, Exchange $exchange): bool
    {
        return true; // All authenticated users can view exchanges
    }

    public function create(User $user): bool
    {
        return false; // Assuming all authenticated users can create exchanges
    }

    public function update(User $user, Exchange $exchange): bool
    {
        return false;
    }

    public function delete(User $user, Exchange $exchange): bool
    {
        return false;
    }
}
