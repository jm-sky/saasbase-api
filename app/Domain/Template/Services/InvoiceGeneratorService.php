<?php

namespace App\Domain\Template\Services;

use App\Domain\Common\Models\Media;
use App\Domain\Invoice\Models\Invoice;
use App\Domain\Template\Enums\TemplateCategory;
use App\Domain\Template\Exceptions\TemplateNotFoundException;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Http\Response;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;
use Mccarlosen\LaravelMpdf\LaravelMpdf;

class InvoiceGeneratorService
{
    public const COLLECTION = 'attachments';

    public function __construct(
        private InvoiceTemplateService $templateService,
        private InvoiceToTemplateTransformer $transformer,
        private TemplatingService $templatingService
    ) {
    }

    /**
     * Generate PDF for an invoice.
     */
    public function generatePdf(Invoice $invoice, ?string $templateId = null, ?string $language = 'pl'): string
    {
        $styledHtml = $this->generateStyledHtml($invoice, $templateId, $language);
        $this->saveHtmlToFile($styledHtml, $invoice->id);
        $pdf = $this->createPdfFromHtml($styledHtml, $invoice, $templateId);

        return $pdf->output();
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
        $pdf        = $this->createPdfFromHtml($styledHtml, $invoice, $templateId);
        $filename   = $this->generateFilename($invoice);

        $symfonyResponse = $pdf->download($filename);

        return response(
            $symfonyResponse->getContent(),
            $symfonyResponse->getStatusCode(),
            $symfonyResponse->headers->all()
        );
    }

    /**
     * Generate PDF and return as stream response.
     */
    public function streamPdf(Invoice $invoice, ?string $templateId = null, ?string $language = 'pl'): \Symfony\Component\HttpFoundation\Response
    {
        $styledHtml = $this->generateStyledHtml($invoice, $templateId, $language);
        $pdf        = $this->createPdfFromHtml($styledHtml, $invoice, $templateId);
        $filename   = $this->generateFilename($invoice);

        return $pdf->stream($filename);
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
    private function generateStyledHtml(Invoice $invoice, ?string $templateId = null, ?string $language = 'pl'): string
    {
        $template     = $this->getTemplate($invoice, $templateId);
        $templateData = $this->transformer->transform($invoice);

        $htmlContent = $this->templatingService->render(
            $template->content,
            ['invoice' => $templateData->toArray()]
        );

        return $this->addCssToHtml($htmlContent, $language);
    }

    /**
     * Create PDF from HTML content.
     */
    private function createPdfFromHtml(string $styledHtml, Invoice $invoice, ?string $templateId = null): LaravelMpdf
    {
        $template = $this->getTemplate($invoice, $templateId);
        $pdf      = PDF::loadHTML($styledHtml);
        $this->applyPdfSettings($pdf, $template->settings ?? []);

        return $pdf;
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

        return $template;
    }

    /**
     * Apply PDF settings from template.
     */
    private function applyPdfSettings(LaravelMpdf $pdf, array $settings): void
    {
        // mPDF configuration - format and orientation
        $format      = 'A4';
        $orientation = $settings['orientation'] ?? 'P'; // P for portrait, L for landscape

        // mPDF uses different format: [width, height] for custom sizes or standard format strings
        // @phpstan-ignore-next-line
        $pdf->getMpdf()->_setPageSize($format, $orientation);

        if (isset($settings['margins'])) {
            $margins = $settings['margins'];
            // @phpstan-ignore-next-line
            $pdf->getMpdf()->SetMargins(
                $margins['left'] ?? 10,
                $margins['right'] ?? 10,
                $margins['top'] ?? 10
            );
            // @phpstan-ignore-next-line
            $pdf->getMpdf()->SetAutoPageBreak(true, $margins['bottom'] ?? 10);
        } else {
            // Default margins
            // @phpstan-ignore-next-line
            $pdf->getMpdf()->SetMargins(10, 10, 10);
            // @phpstan-ignore-next-line
            $pdf->getMpdf()->SetAutoPageBreak(true, 10);
        }

        // mPDF specific configurations for better rendering
        // @phpstan-ignore-next-line
        $pdf->getMpdf()->useSubstitutions = false;
        // @phpstan-ignore-next-line
        $pdf->getMpdf()->simpleTables     = false;
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
        if (!app()->environment('local')) {
            return;
        }

        $filename = "invoice-{$invoiceId}.html";
        $path     = storage_path("app/temp/{$filename}");

        file_put_contents($path, $html);
    }

    /**
     * Add CSS styling to HTML for PDF generation.
     */
    private function addCssToHtml(string $html, ?string $language = 'pl'): string
    {
        $css = $this->loadCss();

        return "<!DOCTYPE html>
<html lang=\"{$language}\">
<head>
    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">
    <meta charset=\"utf-8\">
    <style>{$css}</style>
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
        $cssPath = resource_path('css/invoice-pdf.css');

        if (file_exists($cssPath)) {
            return file_get_contents($cssPath);
        }

        // Fallback to basic CSS if file not found
        return 'body { font-family: Arial, sans-serif; font-size: 12px; }';
    }
}
