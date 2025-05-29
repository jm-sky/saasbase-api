<?php

namespace App\Services;

use App\Domain\Bank\DTO\BankInfoDTO;
use App\Domain\Bank\Models\Bank;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class BankRoutingService
{
    private const CACHE_KEY = 'bank_routing_codes';

    private const CACHE_TTL = 86400; // 24 hours

    public function __construct()
    {
        $this->warmCache();
    }

    public function warmCache(): void
    {
        if (!Cache::has(self::CACHE_KEY)) {
            $this->rebuildCache();
        }
    }

    public function rebuildCache(): void
    {
        $routings = Bank::all()->mapWithKeys(function ($bank) {
            return [$bank->routing_code => $bank];
        });

        Cache::put(self::CACHE_KEY, $routings, self::CACHE_TTL);
    }

    public function getBankInfoFromIban(string $iban, ?string $country = null): ?BankInfoDTO
    {
        $iban    = $this->sanitizeIban($iban);
        $country = $country ? strtoupper($country) : substr($iban, 0, 2);

        if (!$this->isValidIban($iban)) {
            return null;
        }

        // For Polish IBANs, we can look up the bank
        if ('PL' === $country) {
            return $this->handlePolishIban($iban);
        }

        // For non-Polish IBANs or when bank not found, return basic info
        return null;
    }

    public function handlePolishIban(string $iban): ?BankInfoDTO
    {
        $routingCode = $this->extractRoutingCode($iban);
        $bank        = $this->getBankByRoutingCode($routingCode);

        if ($bank) {
            return new BankInfoDTO(
                bankName: $bank->bank_name,
                branchName: $bank->branch_name,
                swift: $bank->swift,
                bankCode: $bank->bank_code,
                routingCode: $bank->routing_code,
            );
        }

        return null;
    }

    protected function extractRoutingCode(string $iban): string
    {
        return substr($iban, 4, 8);
    }

    protected function getBankByRoutingCode(string $routingCode): ?Bank
    {
        /** @var Collection $routings */
        $routings = Cache::get(self::CACHE_KEY);

        return $routings->get($routingCode);
    }

    protected function sanitizeIban(string $iban): string
    {
        return Str::of($iban)->replace(' ', '')->toString();
    }

    protected function isValidIban(string $iban): bool
    {
        // Verify IBAN
        if (!verify_iban($iban)) {
            return false;
        }

        // Get IBAN parts
        $parts = iban_get_parts($iban);

        if (!$parts) {
            return false;
        }

        // For Polish IBANs, we have additional validation
        if ('PL' === $parts['country']) {
            return 28 === strlen($iban) && ctype_digit(substr($iban, 2));
        }

        return true;
    }
}
