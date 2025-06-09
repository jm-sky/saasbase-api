<?php

namespace App\Services\IbanInfo;

use App\Domain\IbanInfo\Models\BankCode;
use App\Services\IbanApi\IbanApiService;
use App\Services\IbanInfo\DTOs\IbanInfoDTO;
use Illuminate\Support\Str;

class IbanInfoService
{
    private const DB_RECORD_TTL_DAYS = 30;

    public function __construct(
        private readonly IbanApiService $ibanApiService,
        private readonly IbanCacheService $ibanCacheService,
    ) {
    }

    public function getBankInfoFromIban(string $iban, ?string $country = null): ?IbanInfoDTO
    {
        $iban = $this->sanitizeIban($iban);

        if (!$this->isValidIban($iban)) {
            return null;
        }

        $countryCode = $this->getCountryCode($iban, $country);
        $bankCode    = $this->extractBankCode($iban);

        // 1. Check Redis cache
        $cachedBank = $this->ibanCacheService->get($countryCode, $bankCode);

        if ($cachedBank) {
            return $this->createDtoFromBankCode($cachedBank, $iban);
        }

        // 2. Check database
        $dbBank = BankCode::where('country_code', $countryCode)
            ->where('bank_code', $bankCode)
            ->first()
        ;

        if ($dbBank) {
            // Re-validate if stale
            if ($dbBank->validated_at->addDays(self::DB_RECORD_TTL_DAYS) < now()) {
                return $this->fetchAndCacheFromApi($iban, $countryCode, $bankCode);
            }
            $this->ibanCacheService->put($dbBank);

            return $this->createDtoFromBankCode($dbBank, $iban);
        }

        // 3. Call IbanApi
        return $this->fetchAndCacheFromApi($iban, $countryCode, $bankCode);
    }

    private function fetchAndCacheFromApi(string $iban, string $countryCode, string $bankCode): ?IbanInfoDTO
    {
        $apiInfo = $this->ibanApiService->getIbanInfo($iban);

        if (!$apiInfo || !$apiInfo->data->bank->bank_name) {
            return null;
        }

        $bankData = [
            'country_code' => $countryCode,
            'bank_code'    => $bankCode,
            'bank_name'    => $apiInfo->data->bank->bank_name,
            'swift'        => $apiInfo->data->bank->bic,
            'currency'     => $apiInfo->data->currency_code,
            'validated_at' => now(),
        ];

        $bank = BankCode::updateOrCreate(
            ['country_code' => $countryCode, 'bank_code' => $bankCode],
            $bankData
        );

        $this->ibanCacheService->put($bank);

        return $this->createDtoFromBankCode($bank, $iban);
    }

    private function createDtoFromBankCode(BankCode $bankCode, string $iban): IbanInfoDTO
    {
        return new IbanInfoDTO(
            iban: $iban,
            bankName: $bankCode->bank_name,
            branchName: null, // Not available in this flow
            swift: $bankCode->swift,
            bankCode: $bankCode->bank_code,
            routingCode: null, // Not applicable for all countries
            currency: $bankCode->currency,
        );
    }

    protected function sanitizeIban(string $iban): string
    {
        return Str::of($iban)->upper()->replace(' ', '')->toString();
    }

    protected function getCountryCode(string $iban, ?string $countryCode = null): string
    {
        return $countryCode ? strtoupper($countryCode) : strtoupper(substr($iban, 0, 2));
    }

    protected function extractBankCode(string $iban): string
    {
        return iban_get_bank_part($iban);
    }

    protected function isValidIban(string $iban): bool
    {
        return verify_iban($iban);
    }
}
