<?php

namespace App\Domain\Template\Services\PdfEngines;

use App\Domain\Template\Contracts\PdfEngineInterface;
use App\Domain\Template\Models\InvoiceTemplate;
use Illuminate\Http\Response;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;
use Mccarlosen\LaravelMpdf\LaravelMpdf;

class MpdfEngine implements PdfEngineInterface
{
    private array $config;

    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->getDefaultConfig(), $config);
    }

    public function generatePdf(string $html, array $settings = []): string
    {
        $pdf = $this->createPdfFromHtml($html, $settings);

        return $pdf->output();
    }

    public function downloadPdf(string $html, string $filename, array $settings = []): Response
    {
        $pdf             = $this->createPdfFromHtml($html, $settings);
        $symfonyResponse = $pdf->download($filename);

        return response(
            $symfonyResponse->getContent(),
            $symfonyResponse->getStatusCode(),
            $symfonyResponse->headers->all()
        );
    }

    public function streamPdf(string $html, string $filename, array $settings = []): \Symfony\Component\HttpFoundation\Response
    {
        $pdf = $this->createPdfFromHtml($html, $settings);

        return $pdf->stream($filename);
    }

    public function getName(): string
    {
        return 'mpdf';
    }

    public function isAvailable(): bool
    {
        return class_exists(\Mpdf\Mpdf::class);
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): self
    {
        $this->config = array_merge($this->config, $config);

        return $this;
    }

    public function applyTemplateSettings(InvoiceTemplate $template): self
    {
        if ($template->settings) {
            $this->config = array_merge($this->config, $template->settings);
        }

        return $this;
    }

    public function cleanup(): void
    {
        // mPDF doesn't require explicit cleanup
    }

    private function createPdfFromHtml(string $html, array $settings = []): LaravelMpdf
    {
        $pdf = PDF::loadHTML($html);
        $this->applyPdfSettings($pdf, array_merge($this->config, $settings));

        return $pdf;
    }

    private function applyPdfSettings(LaravelMpdf $pdf, array $settings): void
    {
        // Format and orientation
        $format      = $settings['format'] ?? 'A4';
        $orientation = $settings['orientation'] ?? 'P';

        // @phpstan-ignore-next-line
        $pdf->getMpdf()->_setPageSize($format, $orientation);

        // Margins
        if (isset($settings['margins'])) {
            $margins = $settings['margins'];
            // @phpstan-ignore-next-line
            $pdf->getMpdf()->SetMargins(
                $margins['left'] ?? 10,
                $margins['right'] ?? 10,
                $margins['top'] ?? 10
            );
            // @phpstan-ignore-next-line
            $pdf->getMpdf()->SetAutoPageBreak(true, $margins['bottom'] ?? 15);
        } else {
            // @phpstan-ignore-next-line
            $pdf->getMpdf()->SetMargins(10, 10, 10);
            // @phpstan-ignore-next-line
            $pdf->getMpdf()->SetAutoPageBreak(true, 15);
        }

        // Add footer if enabled
        if (config('pdf.global.add_footer', true)) {
            $this->addFooter($pdf, $settings);
        }

        // mPDF specific configurations
        // @phpstan-ignore-next-line
        $pdf->getMpdf()->useSubstitutions = $settings['use_substitutions'] ?? false;
        // @phpstan-ignore-next-line
        $pdf->getMpdf()->simpleTables = $settings['simple_tables'] ?? false;
    }

    private function addFooter(LaravelMpdf $pdf, array $settings): void
    {
        $appName     = config('app.name', 'SaasBase');
        $generatedAt = now()->format('Y-m-d H:i:s');
        $footerText  = config('pdf.global.footer_text') ?? "Generated at {$generatedAt} | {$appName} | Page {PAGENO} of {nbpg}";

        $footerHtml = "<div style='text-align: center; font-size: 8px; color: #666; border-top: 1px solid #E5E7EB; padding-top: 5px;'>";
        $footerHtml .= $footerText;
        $footerHtml .= '</div>';

        // @phpstan-ignore-next-line
        $pdf->getMpdf()->SetHTMLFooter($footerHtml);
    }

    private function getDefaultConfig(): array
    {
        return config('pdf.engines.mpdf.config', [
            'format'      => 'A4',
            'orientation' => 'P',
            'margins'     => [
                'left'   => 10,
                'right'  => 10,
                'top'    => 10,
                'bottom' => 15,
            ],
            'use_substitutions' => false,
            'simple_tables'     => false,
        ]);
    }
}
