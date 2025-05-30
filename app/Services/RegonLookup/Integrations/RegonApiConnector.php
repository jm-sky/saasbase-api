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

    public function resolveBaseUrl(): string
    {
        return config('services.regon.api_url', 'https://wyszukiwarkaregon.stat.gov.pl/wsBIR/UslugaBIRzewnPubl.svc');
    }

    public function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/soap+xml',
            'Accept'       => 'application/soap+xml',
        ];
    }

    protected function defaultAuth(): ?Authenticator
    {
        return new RegonAuthenticator();
    }

    protected function resolveLimits(): array
    {
        return [
            Limit::allow(6000)->everyHour(),
            Limit::allow(120)->everyMinute(),
            Limit::allow(3)->everySeconds(1),
        ];
    }

    protected function resolveRateLimitStore(): LaravelCacheStore
    {
        return new LaravelCacheStore(Cache::store());
    }
}
