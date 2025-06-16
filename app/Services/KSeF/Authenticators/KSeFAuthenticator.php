<?php

namespace App\Services\KSeF\Authenticators;

use App\Services\KSeF\Integrations\Requests\InitSessionTokenRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Saloon\Contracts\Authenticator;
use Saloon\Http\PendingRequest;

class KSeFAuthenticator implements Authenticator
{
    public const CACHE_TTL = 60 * 30; // 30 minutes

    protected string $CACHE_KEY = 'ksef:session_token';

    public function __construct(protected ?string $encryptedToken = null)
    {
        $isTest = Str::contains(config('services.ksef.api_url'), 'test');
        $isDemo = Str::contains(config('services.ksef.api_url'), 'demo');

        if ($isTest) {
            $this->CACHE_KEY = "{$this->CACHE_KEY}.test";
        } elseif ($isDemo) {
            $this->CACHE_KEY = "{$this->CACHE_KEY}.demo";
        }
    }

    public function set(PendingRequest $pendingRequest): void
    {
        // Skip authentication for the initial session request
        if ($pendingRequest->getRequest() instanceof InitSessionTokenRequest) {
            return;
        }

        $sessionToken = Cache::remember(
            $this->CACHE_KEY,
            self::CACHE_TTL,
            fn (): string => $this->getSessionToken($pendingRequest)
        );

        $pendingRequest->headers()->add('SessionToken', $sessionToken);
    }

    protected function getSessionToken(PendingRequest $pendingRequest): string
    {
        if (!$this->encryptedToken) {
            throw new \InvalidArgumentException('Encrypted token is required to initialize KSeF session');
        }

        $response = $pendingRequest->getConnector()->send(
            new InitSessionTokenRequest($this->encryptedToken)
        );

        return $response->dto()->sessionToken->token;
    }
}
