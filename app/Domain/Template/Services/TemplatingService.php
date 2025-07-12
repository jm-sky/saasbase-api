<?php

namespace App\Domain\Template\Services;

use App\Domain\Template\Exceptions\TemplateRenderingException;
use Illuminate\Support\Facades\App;
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
                'logoUrl' => function ($url, $options = null) {
                    return new SafeString($this->generateLogoHtml($url, $options));
                },
                'signatureUrl' => function ($url, $options = null) {
                    return new SafeString($this->generateSignatureHtml($url, $options));
                },
            ],
        ];
    }

    /**
     * Render a template with given data.
     */
    public function render(string $template, array $data, ?string $language = null): string
    {
        try {
            if ($language) {
                // Temporarily set locale for this render
                $currentLocale = App::getLocale();
                App::setLocale($language);

                try {
                    $compiled = LightnCandy::compile($template, $this->handlebarsOptions);
                    $renderer = LightnCandy::prepare($compiled);

                    return $renderer($data);
                } finally {
                    // Restore original locale
                    App::setLocale($currentLocale);
                }
            } else {
                $compiled = LightnCandy::compile($template, $this->handlebarsOptions);
                $renderer = LightnCandy::prepare($compiled);

                return $renderer($data);
            }
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

    /**
     * Generate logo HTML with width attribute.
     *
     * @param mixed|null $options
     */
    private function generateLogoHtml(?string $url, $options = null): string
    {
        if (!$url) {
            return '';
        }

        // Extract width from options if it's passed as hash parameter
        $width = null;

        if (is_array($options) && isset($options['hash']['width'])) {
            $width = $options['hash']['width'];
        }

        $attributes = '';

        if ($width) {
            $attributes .= " style=\"max-width: {$width}; height: auto;\"";
        }

        return "<img src=\"{$url}\"{$attributes} alt=\"Logo\" />";
    }

    /**
     * Generate signature HTML with width attribute.
     *
     * @param mixed|null $options
     */
    private function generateSignatureHtml(?string $url, $options = null): string
    {
        if (!$url) {
            return '';
        }

        // Extract width from options if it's passed as hash parameter
        $width = null;

        if (is_array($options) && isset($options['hash']['width'])) {
            $width = $options['hash']['width'];
        }

        $attributes = '';

        if ($width) {
            $attributes .= " style=\"max-width: {$width}; height: auto;\"";
        } else {
            // Default styling for signatures
            $attributes .= ' style="max-width: 150px; height: auto;"';
        }

        return "<img src=\"{$url}\"{$attributes} alt=\"Signature\" />";
    }
}
