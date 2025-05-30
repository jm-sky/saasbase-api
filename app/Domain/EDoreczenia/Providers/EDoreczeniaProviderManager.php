<?php

namespace App\Domain\EDoreczenia\Providers;

use App\Domain\EDoreczenia\Contracts\EDoreczeniaProviderInterface;
use App\Domain\EDoreczenia\Models\EDoreczeniaCertificate;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;

class EDoreczeniaProviderManager
{
    /**
     * @var array<string, class-string<EDoreczeniaProviderInterface>>
     */
    private array $providers = [];

    public function __construct()
    {
        $this->registerDefaultProviders();
    }

    /**
     * Register a new provider.
     *
     * @param class-string<EDoreczeniaProviderInterface> $providerClass
     */
    public function registerProvider(string $providerClass): void
    {
        if (!is_subclass_of($providerClass, EDoreczeniaProviderInterface::class)) {
            throw new InvalidArgumentException("Provider class must implement EDoreczeniaProviderInterface");
        }

        $provider = new $providerClass(
            config('edoreczenia.providers.edo_post.api_key'),
            config('edoreczenia.providers.edo_post.api_secret'),
            config('edoreczenia.providers.edo_post.api_url')
        );

        $this->providers[$provider->getProviderName()] = $provider;
    }

    /**
     * Get all registered providers.
     *
     * @return Collection<EDoreczeniaProviderInterface>
     */
    public function getProviders(): Collection
    {
        return collect($this->providers);
    }

    /**
     * Get a specific provider by name.
     */
    public function getProvider(string $name): ?EDoreczeniaProviderInterface
    {
        return $this->providers[$name] ?? null;
    }

    /**
     * Get the active provider for a tenant.
     */
    public function getTenantProvider(Tenant $tenant): ?EDoreczeniaProviderInterface
    {
        $certificate = EDoreczeniaCertificate::where('tenant_id', $tenant->id)
            ->where('is_valid', true)
            ->first();

        if (!$certificate) {
            return null;
        }

        return $this->getProvider($certificate->provider);
    }

    private function registerDefaultProviders(): void
    {
        $this->registerProvider(EDOPostProvider::class);
    }
}
