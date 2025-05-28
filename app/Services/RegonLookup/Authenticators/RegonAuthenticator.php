<?php

namespace App\Services\RegonLookup\Authenticators;

use Saloon\Contracts\Authenticator;
use Saloon\Http\PendingRequest;

class RegonAuthenticator implements Authenticator
{
    protected string $CACHE_KEY = 'regon_lookup.session_key';

    public function __construct(
        private readonly string $username,
        private readonly string $password
    ) {
    }

    public function set(PendingRequest $request): void
    {
        $request->withHeader('Authorization', 'Basic ' . base64_encode($this->username . ':' . $this->password));
    }
}
