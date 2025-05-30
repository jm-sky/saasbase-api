<?php

namespace App\Domain\EDoreczenia\Services;

use App\Domain\EDoreczenia\Contracts\EDoreczeniaProviderInterface;
use Illuminate\Support\Facades\Config;

class EDoreczeniaProviderManager
{
    private array $providers = [];

    public function __construct()
    {
        $this->registerProviders();
    }

    private function registerProviders(): void
    {
        // Register eDO Post provider
        $this->providers['edo_post'] = new EDOPostProvider(
            Config::get('services.edo_post.base_url'),
            Config::get('services.edo_post.mailbox_address')
        );
    }

    public function getProvider(string $providerName): ?EDoreczeniaProviderInterface
    {
        return $this->providers[$providerName] ?? null;
    }

    public function getAvailableProviders(): array
    {
        return array_keys($this->providers);
    }
}
