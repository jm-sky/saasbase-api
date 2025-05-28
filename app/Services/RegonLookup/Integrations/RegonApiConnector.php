<?php

namespace App\Services\RegonLookup\Integrations;

use App\Services\RegonLookup\Authenticators\RegonAuthenticator;
use App\Services\RegonLookup\Integrations\Responses\RegonResponse;
use Illuminate\Support\Facades\Cache;
use Saloon\Contracts\Authenticator;
use Saloon\Http\Connector;
use Saloon\RateLimitPlugin\Limit;
use Saloon\RateLimitPlugin\Stores\LaravelCacheStore;
use Saloon\RateLimitPlugin\Traits\HasRateLimits;
use Saloon\Traits\Plugins\AcceptsJson;

class RegonApiConnector extends Connector
{
    use AcceptsJson;
    use HasRateLimits;

    protected ?string $response = RegonResponse::class;

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $username,
        private readonly string $password
    ) {
    }

    public function resolveBaseUrl(): string
    {
        return $this->baseUrl;
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    protected function defaultConfig(): array
    {
        return [
            'timeout'         => 30,
            'connect_timeout' => 10,
        ];
    }

    protected function defaultAuthenticator(): ?Authenticator
    {
        return new RegonAuthenticator(
            username: $this->username,
            password: $this->password
        );
    }

    protected function resolveLimits(): array
    {
        return [
            Limit::allow(1)->everyMinute(),
        ];
    }

    protected function resolveRateLimitStore(): LaravelCacheStore
    {
        return new LaravelCacheStore(Cache::store());
    }
}
