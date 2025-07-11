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
    public function generateStyledHtml(Invoice $invoice, ?string $templateId = null, ?string $language = 'pl'): string
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
     * Generate styled HTML from template content and preview data.
     */
    public function generatePreviewHtml(string $templateContent, array $previewData, ?string $language = 'en'): string
    {
        $htmlContent = $this->templatingService->render(
            $templateContent,
            $previewData
        );

        return $this->addCssToHtml($htmlContent, $language);
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
