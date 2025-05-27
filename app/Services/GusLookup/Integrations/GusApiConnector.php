<?php

namespace App\Services\GusLookup\Integrations;

use App\Services\GusLookup\Integrations\Authenticators\GusAuthenticator;
use App\Services\GusLookup\Integrations\Responses\GusResponse;
use Illuminate\Support\Facades\Cache;
use Saloon\Contracts\Authenticator;
use Saloon\Http\Connector;
use Saloon\RateLimitPlugin\Contracts\RateLimitStore;
use Saloon\RateLimitPlugin\Limit;
use Saloon\RateLimitPlugin\Stores\LaravelCacheStore;
use Saloon\RateLimitPlugin\Traits\HasRateLimits;

class GusApiConnector extends Connector
{
    use HasRateLimits;

    public const CACHE_KEY = 'gus_lookup.rate_limit';

    protected ?string $sessionId = null;

    protected ?string $response = GusResponse::class;

    public function resolveBaseUrl(): string
    {
        return config('gus_lookup.api_url', 'https://wyszukiwarkaregon.stat.gov.pl/wsBIR/UslugaBIRzewnPubl.svc');
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function setSessionId(?string $sessionId): void
    {
        $this->sessionId = $sessionId;
    }

    public function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/soap+xml',
            'Accept'       => 'application/soap+xml',
        ];
    }

    protected function resolveLimits(): array
    {
        return [
            Limit::allow(6000)->everyHour(),
            Limit::allow(120)->everyMinute(),
            Limit::allow(3)->everySeconds(1),
        ];
    }

    protected function resolveRateLimitStore(): RateLimitStore
    {
        return new LaravelCacheStore(Cache::store('redis'));
    }

    protected function defaultAuth(): ?Authenticator
    {
        return new GusAuthenticator();
    }
}
