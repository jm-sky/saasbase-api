<?php

namespace App\Services\KSeF\Integrations;

use App\Services\KSeF\Authenticators\KSeFAuthenticator;
use App\Services\KSeF\Integrations\Responses\KSeFResponse;
use Illuminate\Support\Facades\Cache;
use Saloon\Contracts\Authenticator;
use Saloon\Http\Connector;
use Saloon\RateLimitPlugin\Limit;
use Saloon\RateLimitPlugin\Stores\LaravelCacheStore;
use Saloon\RateLimitPlugin\Traits\HasRateLimits;
use Saloon\Traits\Plugins\AcceptsJson;

class KSeFApiConnector extends Connector
{
    use AcceptsJson;
    use HasRateLimits;

    protected ?string $response = KSeFResponse::class;

    public function __construct(
        protected ?string $encryptedToken = null
    ) {
    }

    public function resolveBaseUrl(): string
    {
        return config('services.ksef.api_url', 'https://ksef.mf.gov.pl/api');
    }

    public function defaultHeaders(): array
    {
        return [
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    protected function defaultAuth(): ?Authenticator
    {
        return new KSeFAuthenticator($this->encryptedToken);
    }

    protected function resolveLimits(): array
    {
        return [
            Limit::allow(1000)->everyHour(),
            Limit::allow(60)->everyMinute(),
            Limit::allow(2)->everySeconds(1),
        ];
    }

    protected function resolveRateLimitStore(): LaravelCacheStore
    {
        return new LaravelCacheStore(Cache::store());
    }
}
