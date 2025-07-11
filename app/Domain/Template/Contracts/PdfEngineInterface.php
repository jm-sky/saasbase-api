<?php

namespace App\Domain\Template\Contracts;

use App\Domain\Template\Models\InvoiceTemplate;
use Illuminate\Http\Response;

interface PdfEngineInterface
{
    /**
     * Generate PDF content from HTML.
     */
    public function generatePdf(string $html, array $settings = []): string;

    /**
     * Generate PDF and return as download response.
     */
    public function downloadPdf(string $html, string $filename, array $settings = []): Response;

    /**
     * Generate PDF and return as stream response.
     */
    public function streamPdf(string $html, string $filename, array $settings = []): \Symfony\Component\HttpFoundation\Response;

    /**
     * Get the engine name.
     */
    public function getName(): string;

    /**
     * Check if the engine is available.
     */
    public function isAvailable(): bool;

    /**
     * Get engine configuration.
     */
    public function getConfig(): array;

    /**
     * Set engine configuration.
     */
    public function setConfig(array $config): self;

    /**
     * Apply template-specific settings to the engine.
     */
    public function applyTemplateSettings(InvoiceTemplate $template): self;

    /**
     * Clean up any temporary files or resources.
     */
    public function cleanup(): void;
}
