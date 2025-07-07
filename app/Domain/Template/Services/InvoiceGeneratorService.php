<?php

namespace App\Domain\Template\Services;

use App\Domain\Invoice\Models\Invoice;
use App\Domain\Template\Enums\TemplateCategory;
use App\Domain\Template\Exceptions\TemplateNotFoundException;
use App\Domain\Tenant\Models\Tenant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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
    public function generatePdf(Invoice $invoice, ?string $templateId = null): string
    {
        // Get template
        $template = $this->getTemplate($invoice, $templateId);

        // Transform invoice to template data
        $templateData = $this->transformer->transform($invoice);

        // Render HTML content
        $htmlContent = $this->templatingService->render(
            $template->content,
            $templateData->toArray()
        );

        // Generate PDF
        $pdf = Pdf::loadHTML($htmlContent);

        // Apply template settings if available
        $this->applyPdfSettings($pdf, $template->settings);

        return $pdf->output();
    }

    /**
     * Generate PDF and attach to Invoice model.
     */
    public function generateAndAttachPdf(Invoice $invoice, ?string $templateId = null, string $collection = self::COLLECTION): Media
    {
        // Generate PDF content
        $pdfContent = $this->generatePdf($invoice, $templateId);

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
            $templateData->toArray()
        );

        $pdf = Pdf::loadHTML($htmlContent);
        $this->applyPdfSettings($pdf, $template->settings);

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
            $templateData->toArray()
        );

        $pdf = Pdf::loadHTML($htmlContent);
        $this->applyPdfSettings($pdf, $template->settings);

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

        return $this->templatingService->render(
            $template->content,
            $templateData->toArray()
        );
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
}
