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
        // Get template
        $template = $this->getTemplate($invoice, $templateId);

        // Transform invoice to template data
        $templateData = $this->transformer->transform($invoice);

        // Render HTML content
        $htmlContent = $this->templatingService->render(
            $template->content,
            ['invoice' => $templateData->toArray()]
        );

        // Add CSS styling for Tailwind classes
        $styledHtml = $this->addCssToHtml($htmlContent, $language);

        $this->saveHtmlToFile($styledHtml, $invoice->id);

        // Generate PDF with mPDF
        $pdf = PDF::loadHTML($styledHtml);

        // Apply template settings if available
        $this->applyPdfSettings($pdf, $template->settings ?? []);

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
    public function downloadPdf(Invoice $invoice, ?string $templateId = null): Response
    {
        $template     = $this->getTemplate($invoice, $templateId);
        $templateData = $this->transformer->transform($invoice);

        $htmlContent = $this->templatingService->render(
            $template->content,
            ['invoice' => $templateData->toArray()]
        );

        $styledHtml = $this->addCssToHtml($htmlContent);
        $pdf        = PDF::loadHTML($styledHtml);
        $this->applyPdfSettings($pdf, $template->settings ?? []);

        $filename = $this->generateFilename($invoice);

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
    public function streamPdf(Invoice $invoice, ?string $templateId = null): Response
    {
        $template     = $this->getTemplate($invoice, $templateId);
        $templateData = $this->transformer->transform($invoice);

        $htmlContent = $this->templatingService->render(
            $template->content,
            ['invoice' => $templateData->toArray()]
        );

        $styledHtml = $this->addCssToHtml($htmlContent);
        $pdf        = PDF::loadHTML($styledHtml);
        $this->applyPdfSettings($pdf, $template->settings ?? []);

        $filename = $this->generateFilename($invoice);

        return $pdf->stream($filename);
    }

    /**
     * Preview HTML content without generating PDF.
     */
    public function previewHtml(Invoice $invoice, ?string $templateId = null): string
    {
        $template     = $this->getTemplate($invoice, $templateId);
        $templateData = $this->transformer->transform($invoice);

        $htmlContent = $this->templatingService->render(
            $template->content,
            ['invoice' => $templateData->toArray()]
        );

        return $this->addCssToHtml($htmlContent);
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
        $pdf->getMpdf()->_setPageSize($format, $orientation);

        if (isset($settings['margins'])) {
            $margins = $settings['margins'];
            $pdf->getMpdf()->SetMargins(
                $margins['left'] ?? 10,
                $margins['right'] ?? 10,
                $margins['top'] ?? 10
            );
            $pdf->getMpdf()->SetAutoPageBreak(true, $margins['bottom'] ?? 10);
        } else {
            // Default margins
            $pdf->getMpdf()->SetMargins(10, 10, 10);
            $pdf->getMpdf()->SetAutoPageBreak(true, 10);
        }

        // mPDF specific configurations for better rendering
        $pdf->getMpdf()->useSubstitutions = false;
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
        $css = $this->generateCss();

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
     * Generate CSS for PDF styling.
     */
    private function generateCss(): string
    {
        return '
        /* Base styles */
        body {
            font-family: "DejaVu Sans", "Noto Sans", "Roboto", "Helvetica Neue", Arial, sans-serif;
            font-size: 11px;
            line-height: 1.3;
            color: #333;
            margin: 0;
            padding: 0;
        }

        /* Layout utilities */
        .w-full { width: 100%; }
        .w-1\/2 { width: 50%; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .float-left { float: left; }
        .float-right { float: right; }
        .ml-auto { margin-left: auto; }
        .pr-4 { padding-right: 16px; }
        .pl-4 { padding-left: 16px; }

        /* Flexbox layout (mPDF supports flexbox) */
        .flex { display: flex; }
        .flex-table { display: flex; width: 100%; }
        .flex-row { display: flex; width: 100%; }
        .flex-cell { flex: 1; }
        .flex-cell-center { flex: 1; align-items: center; }
        .justify-between { justify-content: space-between; }
        .items-center { align-items: center; }

        /* Typography */
        .font-bold { font-weight: bold; }
        .font-semibold { font-weight: 600; }
        .font-medium { font-weight: 500; }
        .font-light { font-weight: 300; }
        .text-xs { font-size: 8px; }
        .text-sm { font-size: 9px; }
        .text-base { font-size: 10px; }
        .text-lg { font-size: 12px; }
        .text-xl { font-size: 14px; }
        .text-2xl { font-size: 16px; }
        .text-3xl { font-size: 20px; }

        /* Spacing */
        .mb-1 { margin-bottom: 4px; }
        .mb-2 { margin-bottom: 8px; }
        .mb-4 { margin-bottom: 12px; }
        .mb-6 { margin-bottom: 20px; }
        .mb-8 { margin-bottom: 24px; }
        .mt-2 { margin-top: 6px; }
        .mt-4 { margin-top: 12px; }
        .mt-6 { margin-top: 20px; }
        .mt-8 { margin-top: 24px; }
        .p-2 { padding: 6px; }
        .p-4 { padding: 12px; }
        .p-6 { padding: 20px; }
        .py-1 { padding-top: 3px; padding-bottom: 3px; }
        .py-2 { padding-top: 6px; padding-bottom: 6px; }
        .py-3 { padding-top: 8px; padding-bottom: 8px; }
        .px-2 { padding-left: 6px; padding-right: 6px; }
        .px-4 { padding-left: 12px; padding-right: 12px; }
        .pt-2 { padding-top: 6px; }
        .pt-4 { padding-top: 12px; }
        .pb-1 { padding-bottom: 3px; }

        /* Colors */
        .accent-bg { background-color: #3B82F6; }
        .accent-text { color: #3B82F6; }
        .accent-border { border-color: #3B82F6; }
        .secondary-text { color: #6B7280; }
        .text-white { color: white; }
        .text-gray-600 { color: #4B5563; }
        .text-gray-900 { color: #111827; }
        .bg-gray-50 { background-color: #F9FAFB; }
        .bg-gray-800 { background-color: #1F2937; }

        /* Borders */
        .border { border: 1px solid #D1D5DB; }
        .border-b { border-bottom: 1px solid #D1D5DB; }
        .border-t { border-top: 1px solid #D1D5DB; }
        .border-gray-300 { border-color: #D1D5DB; }
        .border-gray-200 { border-color: #E5E7EB; }

        /* Tables */
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .invoice-table th,
        .invoice-table td {
            border: 1px solid #E5E7EB;
            padding: 6px;
            text-align: left;
        }
        .invoice-table th {
            background-color: #3B82F6;
            color: white;
            font-weight: bold;
        }

        /* Grid replacement with table */
        .grid { display: table; width: 100%; }
        .grid-cols-2 { display: table-row; }
        .grid-cols-2 > div { display: table-cell; width: 50%; vertical-align: top; padding-right: 12px; }
        .grid-cols-2 > div:last-child { padding-right: 0; }

        /* Container */
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 12px;
        }

        /* Clear floats */
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        /* Negative margins fix */
        .-mx-6 { margin-left: -20px; margin-right: -20px; }
        ';
    }
}
