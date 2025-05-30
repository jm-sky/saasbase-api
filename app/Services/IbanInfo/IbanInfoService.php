<?php

namespace App\Services\IbanInfo;

use App\Domain\Bank\DTO\BankInfoDTO;
use App\Domain\Bank\Models\Bank;
use App\Services\IbanApi\IbanApiService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class IbanInfoService
{
    private const POLISH_COUNTRY_CODE = 'PL';

    private const POLISH_CURRENCY_CODE = 'PLN';

    private const CACHE_KEY = 'bank_routing_codes';

    private const CACHE_TTL = 86400; // 24 hours

    public function __construct(
        private ?IbanApiService $ibanApiService = null,
    ) {
        $this->ibanApiService = $ibanApiService ?? new IbanApiService();
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
        $country = $this->getCountryCode($iban, $country);

        if (!$this->isValidIban($iban)) {
            return null;
        }

        // For Polish IBANs, we can look up the bank
        switch ($country) {
            case self::POLISH_COUNTRY_CODE:
                return $this->handlePolishIban($iban);
            default:
                return $this->handleNonPolishIban($iban, $country);
        }
    }

    public function handlePolishIban(string $iban): ?BankInfoDTO
    {
        $routingCode = $this->extractRoutingCode($iban);
        $bank        = $this->getBankByRoutingCode($routingCode);

        if ($bank) {
            return new BankInfoDTO(
                iban: $iban,
                bankName: $bank->bank_name,
                branchName: $bank->branch_name,
                swift: $bank->swift ?? $this->ibanApiService?->getSwiftForIban($iban, false) ?? null,
                bankCode: $bank->bank_code,
                routingCode: $bank->routing_code,
                currency: self::POLISH_CURRENCY_CODE,
            );
        }

        return $this->getBankInfoFromServices($iban, self::POLISH_COUNTRY_CODE);
    }

    public function handleNonPolishIban(string $iban, string $countryCode): ?BankInfoDTO
    {
        return $this->getBankInfoFromServices($iban, $countryCode);
    }

    public function getBankInfoFromServices(string $iban, string $countryCode): ?BankInfoDTO
    {
        $info = $this->ibanApiService?->getIbanInfo($iban);

        return new BankInfoDTO(
            iban: $iban,
            bankName: $info?->data->bank->bank_name ?? iban_country_get_central_bank_name($countryCode),
            branchName: null,
            swift: $info?->data->bank?->bic ?? null,
            bankCode: iban_get_bank_part($iban),
            routingCode: null,
            currency: $info?->data->currency_code ?? iban_country_get_currency_iso4217($countryCode),
        );
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

    protected function getCountryCode(string $iban, ?string $countryCode = null): string
    {
        return $countryCode ? strtoupper($countryCode) : strtoupper(substr($iban, 0, 2));
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
        if (self::POLISH_COUNTRY_CODE === $parts['country']) {
            return $this->isValidPolishIban($iban);
        }

        return true;
    }

    protected function isValidPolishIban(string $iban): bool
    {
        $polishIbanLength = 28;

        return $polishIbanLength === strlen($iban) && ctype_digit(substr($iban, 2));
    }
}
