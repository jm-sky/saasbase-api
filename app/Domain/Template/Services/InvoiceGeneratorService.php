<?php

namespace App\Domain\Template\Services;

use App\Domain\Common\Models\Media;
use App\Domain\Invoice\Models\Invoice;
use App\Domain\Template\Enums\TemplateCategory;
use App\Domain\Template\Exceptions\TemplateNotFoundException;
use App\Domain\Tenant\Models\Tenant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

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

        // Generate PDF
        $pdf = Pdf::loadHTML($styledHtml);

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
        $pdf        = Pdf::loadHTML($styledHtml);
        $this->applyPdfSettings($pdf, $template->settings ?? []);

        $filename = $this->generateFilename($invoice);

        return $pdf->download($filename);
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
        $pdf        = Pdf::loadHTML($styledHtml);
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
    private function applyPdfSettings($pdf, array $settings): void
    {
        if (isset($settings['orientation'])) {
            $pdf->setPaper('A4', $settings['orientation']);
        } else {
            $pdf->setPaper('A4', 'portrait');
        }

        if (isset($settings['margins'])) {
            $margins = $settings['margins'];
            $pdf->setOption('margin-top', $margins['top'] ?? 10);
            $pdf->setOption('margin-right', $margins['right'] ?? 10);
            $pdf->setOption('margin-bottom', $margins['bottom'] ?? 10);
            $pdf->setOption('margin-left', $margins['left'] ?? 10);
        }

        // Additional PDF options
        $pdf->setOption('enable-local-file-access', true);
        $pdf->setOption('isRemoteEnabled', true);
    }

    /**
     * Generate filename for PDF.
     */
    private function generateFilename(Invoice $invoice): string
    {
        $number = str_replace(['/', '\\'], '-', $invoice->number);

        return "invoice-{$number}.pdf";
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
            font-family: "DejaVu Sans", "Roboto", "Helvetica Neue", Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }

        /* Layout utilities */
        .w-full { width: 100%; }
        .w-1\/2 { width: 50%; }
        .flex { display: flex; }
        .justify-between { justify-content: space-between; }
        .items-center { align-items: center; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .float-left { float: left; }
        .float-right { float: right; }
        .ml-auto { margin-left: auto; }

        /* Typography */
        .font-bold { font-weight: bold; }
        .font-semibold { font-weight: 600; }
        .font-medium { font-weight: 500; }
        .font-light { font-weight: 300; }
        .text-sm { font-size: 11px; }
        .text-base { font-size: 12px; }
        .text-lg { font-size: 14px; }
        .text-xl { font-size: 16px; }
        .text-2xl { font-size: 20px; }
        .text-3xl { font-size: 24px; }

        /* Spacing */
        .mb-2 { margin-bottom: 8px; }
        .mb-4 { margin-bottom: 16px; }
        .mb-6 { margin-bottom: 24px; }
        .mb-8 { margin-bottom: 32px; }
        .mt-4 { margin-top: 16px; }
        .mt-6 { margin-top: 24px; }
        .mt-8 { margin-top: 32px; }
        .p-4 { padding: 16px; }
        .p-6 { padding: 24px; }
        .py-2 { padding-top: 8px; padding-bottom: 8px; }
        .py-3 { padding-top: 12px; padding-bottom: 12px; }
        .px-4 { padding-left: 16px; padding-right: 16px; }
        .pt-4 { padding-top: 16px; }
        .pb-1 { padding-bottom: 4px; }

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
            margin-bottom: 16px;
        }
        .invoice-table th,
        .invoice-table td {
            border: 1px solid #E5E7EB;
            padding: 8px;
            text-align: left;
        }
        .invoice-table th {
            background-color: #3B82F6;
            color: white;
            font-weight: bold;
        }

        /* Grid */
        .grid { display: table; width: 100%; }
        .grid-cols-2 { }
        .grid-cols-2 > div { display: table-cell; width: 50%; vertical-align: top; }
        .gap-4 > * { margin-right: 16px; }

        /* Container */
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Clear floats */
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        /* Negative margins fix */
        .-mx-6 { margin-left: -24px; margin-right: -24px; }
        ';
    }
}
