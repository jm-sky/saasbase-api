<?php

namespace App\Domain\Template\Services;

use App\Domain\Template\Contracts\PdfEngineInterface;
use App\Domain\Template\Services\PdfEngines\MpdfEngine;
use App\Domain\Template\Services\PdfEngines\PuppeteerEngine;

class PdfEngineFactory
{
    public static function create(?string $engine = null): PdfEngineInterface
    {
        $engine = $engine ?? config('pdf.default', 'mpdf');

        return match ($engine) {
            'mpdf'      => new MpdfEngine(),
            'puppeteer' => new PuppeteerEngine(),
            default     => throw new \InvalidArgumentException("Unknown PDF engine: {$engine}"),
        };
    }

    public static function createWithConfig(string $engine, array $config = []): PdfEngineInterface
    {
        $pdfEngine = self::create($engine);

        if (!empty($config)) {
            $pdfEngine->setConfig($config);
        }

        return $pdfEngine;
    }

    public static function getAvailableEngines(): array
    {
        $engines = [];

        foreach (['mpdf', 'puppeteer'] as $engine) {
            try {
                $instance = self::create($engine);

                if ($instance->isAvailable()) {
                    $engines[$engine] = $instance->getName();
                }
            } catch (\Exception $e) {
                // Engine not available
            }
        }

        return $engines;
    }
}
