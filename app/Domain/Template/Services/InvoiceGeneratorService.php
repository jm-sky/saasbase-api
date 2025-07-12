<?php

namespace App\Domain\Template\Services;

use App\Domain\Common\Models\Media;
use App\Domain\Invoice\Models\Invoice;
use App\Domain\Template\Contracts\PdfEngineInterface;
use App\Domain\Template\Enums\TemplateCategory;
use App\Domain\Template\Exceptions\TemplateNotFoundException;
use App\Domain\Template\Models\InvoiceTemplate;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class InvoiceGeneratorService
{
    public const COLLECTION = 'attachments';

    public ?InvoiceTemplate $lastUsedInvoiceTemplate = null;

    private PdfEngineInterface $pdfEngine;

    public function __construct(
        private InvoiceTemplateService $templateService,
        private InvoiceToTemplateTransformer $transformer,
        private TemplatingService $templatingService,
        ?PdfEngineInterface $pdfEngine = null
    ) {
        $this->pdfEngine = $pdfEngine ?? PdfEngineFactory::create();
    }

    /**
     * Generate PDF for an invoice.
     */
    public function generatePdf(Invoice $invoice, ?string $templateId = null, ?string $language = 'pl'): string
    {
        $styledHtml = $this->generateStyledHtml($invoice, $templateId, $language);
        $this->saveHtmlToFile($styledHtml, $invoice->id);

        $template = $this->getTemplate($invoice, $templateId);
        $this->pdfEngine->applyTemplateSettings($template);

        return $this->pdfEngine->generatePdf($styledHtml);
    }

    /**
     * Generate PDF and attach to Invoice model.
     */
    public function generateAndAttachPdf(Invoice $invoice, ?string $templateId = null, string $collection = self::COLLECTION, ?string $language = 'pl'): Media
    {
        // Generate PDF content
        $pdfContent = $this->generatePdf($invoice, $templateId, $language);

        // Create temporary file
        $filename = $this->generateFilename($invoice);
        $tempPath = storage_path("app/temp/{$filename}");

        // Ensure temp directory exists
        if (!file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        // Write PDF content to temporary file
        file_put_contents($tempPath, $pdfContent);

        try {
            // Remove existing PDF from the same collection if exists
            $existingMedia = $invoice->getMedia($collection)
                ->filter(function (Media $media) {
                    return true === $media->getCustomProperty('generated', false);
                })
                ->first()
            ;

            if ($existingMedia) {
                $existingMedia->delete();
            }

            // Add PDF to invoice media collection
            // @phpstan-ignore-next-line
            return $invoice
                ->addMedia($tempPath)
                ->withCustomProperties([
                    'generated'   => true,
                    'generator'   => 'invoice_generator',
                    'template_id' => $templateId,
                ])
                ->usingName("Invoice {$invoice->number}")
                ->usingFileName($filename)
                ->toMediaCollection($collection)
            ;
        } finally {
            // Clean up temporary file
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }
    }

    /**
     * Generate PDF and return as download response.
     */
    public function downloadPdf(Invoice $invoice, ?string $templateId = null, ?string $language = 'pl'): Response
    {
        $styledHtml = $this->generateStyledHtml($invoice, $templateId, $language);
        $template   = $this->getTemplate($invoice, $templateId);
        $filename   = $this->generateFilename($invoice);

        $this->pdfEngine->applyTemplateSettings($template);

        return $this->pdfEngine->downloadPdf($styledHtml, $filename);
    }

    /**
     * Generate PDF and return as stream response.
     */
    public function streamPdf(Invoice $invoice, ?string $templateId = null, ?string $language = 'pl'): \Symfony\Component\HttpFoundation\Response
    {
        $styledHtml = $this->generateStyledHtml($invoice, $templateId, $language);
        $template   = $this->getTemplate($invoice, $templateId);
        $filename   = $this->generateFilename($invoice);

        $this->pdfEngine->applyTemplateSettings($template);

        return $this->pdfEngine->streamPdf($styledHtml, $filename);
    }

    /**
     * Preview HTML content without generating PDF.
     */
    public function previewHtml(Invoice $invoice, ?string $templateId = null, ?string $language = 'pl'): string
    {
        return $this->generateStyledHtml($invoice, $templateId, $language);
    }

    /**
     * Generate styled HTML for invoice.
     */
    public function generateStyledHtml(Invoice $invoice, ?string $templateId = null, ?string $language = null): string
    {
        $template     = $this->getTemplate($invoice, $templateId);
        $templateData = $this->transformer->transform($invoice);

        $resolvedLanguage = $this->resolveLanguage($language, $templateData->options);

        $htmlContent = $this->templatingService->render(
            $template->content,
            ['invoice' => $templateData->toArray()],
            $resolvedLanguage
        );

        return $this->addCssToHtml($htmlContent, $resolvedLanguage, $templateData->options);
    }

    /**
     * Generate styled HTML from template content and preview data.
     */
    public function generatePreviewHtml(string $templateContent, array $previewData, ?string $language = null, array $options = []): string
    {
        $resolvedLanguage = $this->resolveLanguage($language, $options);

        $htmlContent = $this->templatingService->render(
            $templateContent,
            $previewData,
            $resolvedLanguage
        );

        return $this->addCssToHtml($htmlContent, $resolvedLanguage, $options);
    }

    /**
     * Set PDF engine for this service instance.
     */
    public function setPdfEngine(PdfEngineInterface $pdfEngine): self
    {
        $this->pdfEngine = $pdfEngine;

        return $this;
    }

    /**
     * Get the current PDF engine.
     */
    public function getPdfEngine(): PdfEngineInterface
    {
        return $this->pdfEngine;
    }

    /**
     * Get template for invoice.
     */
    private function getTemplate(Invoice $invoice, ?string $templateId = null)
    {
        if ($templateId) {
            return $this->templateService->findById($templateId);
        }

        // Get default template for invoice category
        $template = $this->templateService->getDefaultForCategory(
            $invoice->tenant_id,
            TemplateCategory::INVOICE
        );

        if (!$template) {
            $template = $this->templateService->getDefaultForCategory(
                Tenant::GLOBAL_TENANT_ID,
                TemplateCategory::INVOICE
            );
        }

        if (!$template) {
            throw new TemplateNotFoundException('No default invoice template found for tenant');
        }

        $this->lastUsedInvoiceTemplate = $template;

        return $template;
    }

    /**
     * Generate filename for PDF.
     */
    private function generateFilename(Invoice $invoice): string
    {
        $number = str_replace(['/', '\\'], '-', $invoice->number);

        return "invoice-{$number}.pdf";
    }

    private function saveHtmlToFile(string $html, string $invoiceId): void
    {
        if (!config('pdf.global.save_html_debug', false)) {
            return;
        }

        $filename = "invoice-{$invoiceId}.html";
        $path     = storage_path("app/temp/{$filename}");

        // Ensure temp directory exists
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, $html);
    }

    /**
     * Add CSS styling to HTML for PDF generation.
     */
    private function addCssToHtml(string $html, ?string $language = 'pl', array $options = []): string
    {
        $css       = $this->loadCss();
        $customCss = $this->generateCustomCss($options);

        return "<!DOCTYPE html>
<html lang=\"{$language}\">
<head>
    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">
    <meta charset=\"utf-8\">
    <style>{$css}{$customCss}</style>
</head>
<body>
    {$html}
</body>
</html>";
    }

    /**
     * Load CSS from external file.
     */
    private function loadCss(): string
    {
        // Determine which CSS file to use based on PDF engine
        $engineName = $this->pdfEngine->getName();

        if ('mpdf' === $engineName || false !== strpos(strtolower($engineName), 'mpdf')) {
            // Use legacy CSS for mPDF (no CSS variables)
            $cssPath = resource_path('css/invoice-pdf-legacy.css');
        } else {
            // Use modern CSS for Puppeteer (with CSS variables)
            $cssPath = resource_path('css/invoice-pdf.css');
        }

        if (file_exists($cssPath)) {
            return file_get_contents($cssPath);
        }

        // Try fallback to the other CSS file
        $fallbackPath = $cssPath === resource_path('css/invoice-pdf.css')
            ? resource_path('css/invoice-pdf-legacy.css')
            : resource_path('css/invoice-pdf.css');

        if (file_exists($fallbackPath)) {
            return file_get_contents($fallbackPath);
        }

        // Last resort: basic CSS
        return 'body { font-family: Arial, sans-serif; font-size: 12px; }';
    }

    /**
     * Generate custom CSS with color overrides based on options and tenant branding.
     */
    private function generateCustomCss(array $options = []): string
    {
        // Always get accent color (with fallback chain)
        $accentColor    = $this->resolveAccentColor($options);
        $secondaryColor = $this->resolveSecondaryColor($options);

        // Always generate custom CSS to override defaults if needed
        $customCss = "\n\n/* Dynamic Color Overrides */\n:root {\n";

        // Accent color is always set (either custom or default)
        $customCss .= "    --accent-primary: {$accentColor};\n";
        $customCss .= "    --primary-600: {$accentColor};\n";
        $customCss .= "    --primary-500: {$this->lightenColor($accentColor, 10)};\n";
        $customCss .= "    --primary-700: {$this->darkenColor($accentColor, 10)};\n";

        if ($secondaryColor) {
            $customCss .= "    --accent-secondary: {$secondaryColor};\n";
        }

        $customCss .= "}\n";

        return $customCss;
    }

    /**
     * Resolve accent color with priority: request > TenantBranding.color_primary > sky-600 default.
     */
    private function resolveAccentColor(array $options): string
    {
        // 1. First check request options (for preview)
        if (!empty($options['accentColor'])) {
            $color = $this->validateColor($options['accentColor']);

            if ($color) {
                return $color;
            }
        }

        // 2. Then check tenant branding color_primary
        if (!empty($options['tenant_id'])) {
            $tenantColors = $this->getTenantBrandingColors($options['tenant_id']);

            if (!empty($tenantColors['primary'])) {
                return $tenantColors['primary'];
            }
        }

        // 3. Default to app's primary color (sky-600)
        return '#0284c7';
    }

    /**
     * Resolve secondary color from options or tenant branding.
     */
    private function resolveSecondaryColor(array $options): ?string
    {
        // First check options (for preview)
        if (!empty($options['secondaryColor'])) {
            return $this->validateColor($options['secondaryColor']);
        }

        // Then check tenant branding
        if (!empty($options['tenant_id'])) {
            $tenantColors = $this->getTenantBrandingColors($options['tenant_id']);

            return $tenantColors['secondary'] ?? null;
        }

        return null;
    }

    /**
     * Get tenant branding colors from database.
     */
    private function getTenantBrandingColors(string $tenantId): array
    {
        try {
            $branding = \App\Domain\Tenant\Models\TenantBranding::where('tenant_id', $tenantId)->first();

            if (!$branding) {
                return [];
            }

            return [
                'primary'   => $this->validateColor($branding->color_primary),
                'accent'    => $this->validateColor($branding->pdf_accent_color),
                'secondary' => $this->validateColor($branding->color_secondary),
            ];
        } catch (\Exception $e) {
            // Log error but don't fail the entire generation
            Log::warning('Failed to load tenant branding colors', [
                'tenant_id' => $tenantId,
                'error'     => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Validate and normalize color value.
     */
    private function validateColor(?string $color): ?string
    {
        if (!$color) {
            return null;
        }

        $color = trim($color);

        // Ensure color has # prefix for hex colors
        if (preg_match('/^[0-9a-fA-F]{6}$/', $color)) {
            return "#{$color}";
        }

        // Validate hex color with #
        if (preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            return $color;
        }

        // Accept rgb() format
        if (preg_match('/^rgb\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*\)$/', $color)) {
            return $color;
        }

        return null;
    }

    /**
     * Lighten a hex color by a percentage.
     */
    private function lightenColor(string $color, int $percent): string
    {
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            return $color; // Return original if not valid hex
        }

        $hex = ltrim($color, '#');
        $r   = hexdec(substr($hex, 0, 2));
        $g   = hexdec(substr($hex, 2, 2));
        $b   = hexdec(substr($hex, 4, 2));

        $r = min(255, $r + ($percent * 255 / 100));
        $g = min(255, $g + ($percent * 255 / 100));
        $b = min(255, $b + ($percent * 255 / 100));

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    /**
     * Darken a hex color by a percentage.
     */
    private function darkenColor(string $color, int $percent): string
    {
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            return $color; // Return original if not valid hex
        }

        $hex = ltrim($color, '#');
        $r   = hexdec(substr($hex, 0, 2));
        $g   = hexdec(substr($hex, 2, 2));
        $b   = hexdec(substr($hex, 4, 2));

        $r = max(0, $r - ($percent * 255 / 100));
        $g = max(0, $g - ($percent * 255 / 100));
        $b = max(0, $b - ($percent * 255 / 100));

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    /**
     * Resolve language with priority: explicit param > options.language > current app locale.
     */
    private function resolveLanguage(?string $language, array $options): string
    {
        // 1. First check explicit parameter
        if ($language) {
            return $language;
        }

        // 2. Then check options (from request)
        if (!empty($options['language'])) {
            return $options['language'];
        }

        // 3. Default to current app locale (set by middleware)
        return App::getLocale();
    }
}
