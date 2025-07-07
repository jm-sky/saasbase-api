<?php

namespace App\Domain\Template\Services;

use Brick\Math\BigDecimal;

class CurrencyFormatterService
{
    /**
     * Format a BigDecimal or string amount with currency.
     */
    public function format(BigDecimal|string $amount, string $currency = 'PLN', int $decimals = 2): string
    {
        if ($amount instanceof BigDecimal) {
            $formatted = number_format($amount->toFloat(), $decimals, ',', ' ');
        } else {
            $formatted = number_format((float) $amount, $decimals, ',', ' ');
        }

        return "{$formatted} {$currency}";
    }

    /**
     * Format amount without currency symbol.
     */
    public function formatWithoutCurrency(BigDecimal|string $amount, int $decimals = 2): string
    {
        if ($amount instanceof BigDecimal) {
            return number_format($amount->toFloat(), $decimals, ',', ' ');
        }

        return number_format((float) $amount, $decimals, ',', ' ');
    }
}
