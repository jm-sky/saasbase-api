<?php

namespace App\Domain\Template\Services;

use App\Domain\Template\Exceptions\TemplateRenderingException;
use LightnCandy\LightnCandy;
use LightnCandy\SafeString;

class TemplatingService
{
    private array $handlebarsOptions;

    public function __construct()
    {
        $this->handlebarsOptions = [
            'flags' => LightnCandy::FLAG_HANDLEBARS |
                       LightnCandy::FLAG_ERROR_EXCEPTION |
                       LightnCandy::FLAG_PROPERTY |
                       LightnCandy::FLAG_SPVARS |
                       LightnCandy::FLAG_RUNTIMEPARTIAL,
            'helpers' => [
                't' => function ($text) {
                    return new SafeString(__($text));
                },
                'formatCurrency' => function ($amount, $currency = 'PLN') {
                    return new SafeString($this->formatCurrency($amount, $currency));
                },
                'formatDate' => function ($date, $format = 'Y-m-d') {
                    return new SafeString($this->formatDate($date, $format));
                },
                'formatNumber' => function ($number, $decimals = 2) {
                    return new SafeString(number_format((float) $number, $decimals, ',', ' '));
                },
                'upper' => function ($text) {
                    return new SafeString(strtoupper($text));
                },
                'lower' => function ($text) {
                    return new SafeString(strtolower($text));
                },
                'ifEquals' => function ($value1, $value2, $options) {
                    return $value1 === $value2 ? $options['fn']() : $options['inverse']();
                },
                'ifNotEmpty' => function ($value, $options) {
                    return !empty($value) ? $options['fn']() : $options['inverse']();
                },
            ],
        ];
    }

    /**
     * Render a template with given data.
     */
    public function render(string $template, array $data): string
    {
        try {
            $compiled = LightnCandy::compile($template, $this->handlebarsOptions);
            $renderer = LightnCandy::prepare($compiled);

            return $renderer($data);
        } catch (\Exception $e) {
            throw new TemplateRenderingException("Failed to render template: {$e->getMessage()}", $e);
        }
    }

    /**
     * Validate template syntax.
     */
    public function validate(string $template): bool
    {
        try {
            LightnCandy::compile($template, $this->handlebarsOptions);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get validation errors for a template.
     */
    public function getValidationErrors(string $template): ?string
    {
        try {
            LightnCandy::compile($template, $this->handlebarsOptions);

            return null;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Format currency amount.
     */
    private function formatCurrency(string $amount, string $currency): string
    {
        $formatted = number_format((float) $amount, 2, ',', ' ');

        return "{$formatted} {$currency}";
    }

    /**
     * Format date.
     */
    private function formatDate(string $date, string $format): string
    {
        try {
            $dateObj = new \DateTime($date);

            return $dateObj->format($format);
        } catch (\Exception $e) {
            return $date; // Return original if parsing fails
        }
    }
}
