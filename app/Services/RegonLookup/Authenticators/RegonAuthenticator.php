<?php

namespace App\Services\RegonLookup\Authenticators;

use App\Services\RegonLookup\Integrations\Requests\LoginRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Saloon\Contracts\Authenticator;
use Saloon\Http\PendingRequest;

class RegonAuthenticator implements Authenticator
{
    public const CACHE_TTL = 60 * 60; // 60 minutes

    protected string $CACHE_KEY = 'regon_lookup:session_key';

    public function __construct()
    {
        $isTest = Str::contains(config('regon_lookup.api_url'), 'wyszukiwarkaregontest');

        $this->CACHE_KEY = $isTest ? "{$this->CACHE_KEY}.test" : "{$this->CACHE_KEY}";
    }

    public function set(PendingRequest $pendingRequest): void
    {
        if ($pendingRequest->getRequest() instanceof LoginRequest) {
            return;
        }

        $sessionKey = Cache::remember(
            $this->CACHE_KEY,
            self::CACHE_TTL,
            fn (): string => $pendingRequest->getConnector()->send(new LoginRequest())->dto()->sessionKey,
        );

        $pendingRequest->headers()->add('sid', $sessionKey);
    }
}
