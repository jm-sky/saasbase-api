<?php

namespace App\Domain\Tenant\Support;

use App\Domain\Tenant\Enums\TenantIntegrationType;

/**
 * Custom-mode integration credentials let a tenant point an integration at
 * their own endpoint (e.g. a dedicated Azure resource). Without a host
 * allowlist this is an SSRF primitive: the backend will send requests
 * (including documents with contractor NIP/IBAN data, for OCR) to whatever
 * URL is stored, with no restriction on internal/private addresses.
 */
class IntegrationAllowedHosts
{
    private const ALLOWED_HOST_SUFFIXES = [
        TenantIntegrationType::AzureAi->value => ['.cognitiveservices.azure.com', '.services.ai.azure.com'],
    ];

    public static function isAllowed(string $type, string $endpoint): bool
    {
        $suffixes = self::ALLOWED_HOST_SUFFIXES[$type] ?? null;

        // Types with no configured allowlist don't support a custom endpoint yet.
        if ($suffixes === null) {
            return false;
        }

        $scheme = parse_url($endpoint, PHP_URL_SCHEME);
        $host = parse_url($endpoint, PHP_URL_HOST);

        if ($scheme !== 'https' || ! $host) {
            return false;
        }

        $host = strtolower($host);

        foreach ($suffixes as $suffix) {
            if (str_ends_with($host, $suffix)) {
                return true;
            }
        }

        return false;
    }
}
