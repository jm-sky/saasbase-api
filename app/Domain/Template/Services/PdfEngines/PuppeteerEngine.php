<?php

namespace App\Domain\Template\Services\PdfEngines;

use App\Domain\Template\Contracts\PdfEngineInterface;
use App\Domain\Template\Models\InvoiceTemplate;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PuppeteerEngine implements PdfEngineInterface
{
    private array $config;

    private array $tempFiles = [];

    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->getDefaultConfig(), $config);
    }

    public function generatePdf(string $html, array $settings = []): string
    {
        $htmlFile = $this->saveHtmlToTempFile($html);
        $pdfFile  = $this->generateTempPdfPath();

        try {
            $this->runPuppeteerScript($htmlFile, $pdfFile, array_merge($this->config, $settings));

            if (!file_exists($pdfFile)) {
                throw new \RuntimeException('PDF file was not generated');
            }

            $pdfContent = file_get_contents($pdfFile);

            if (false === $pdfContent) {
                throw new \RuntimeException('Failed to read generated PDF content');
            }

            return $pdfContent;
        } finally {
            $this->addTempFile($htmlFile);
            $this->addTempFile($pdfFile);

            if (config('pdf.global.temp_cleanup', true)) {
                $this->cleanup();
            }
        }
    }

    public function downloadPdf(string $html, string $filename, array $settings = []): Response
    {
        $pdfContent = $this->generatePdf($html, $settings);

        return response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Content-Length'      => strlen($pdfContent),
        ]);
    }

    public function streamPdf(string $html, string $filename, array $settings = []): \Symfony\Component\HttpFoundation\Response
    {
        $pdfContent = $this->generatePdf($html, $settings);

        return response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
            'Content-Length'      => strlen($pdfContent),
        ]);
    }

    public function getName(): string
    {
        return 'puppeteer';
    }

    public function isAvailable(): bool
    {
        $nodeExecutable = config('pdf.puppeteer.node_executable', 'node');
        $npmExecutable  = config('pdf.puppeteer.npm_executable', 'npm');

        // Check if Node.js is available
        exec("which {$nodeExecutable}", $output, $returnCode);

        if (0 !== $returnCode) {
            return false;
        }

        // Check if npm is available
        exec("which {$npmExecutable}", $output, $returnCode);

        if (0 !== $returnCode) {
            return false;
        }

        // Check if Puppeteer package is actually installed
        exec("{$nodeExecutable} -e \"require('puppeteer')\" 2>/dev/null", $output, $returnCode);

        if (0 !== $returnCode) {
            return false;
        }

        return true;
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
            $templateSettings = $this->convertMpdfToPuppeteerSettings($template->settings);
            $this->config     = array_merge($this->config, $templateSettings);
        }

        return $this;
    }

    public function cleanup(): void
    {
        foreach ($this->tempFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
        $this->tempFiles = [];
    }

    private function runPuppeteerScript(string $htmlFile, string $pdfFile, array $settings): void
    {
        $scriptContent = $this->generatePuppeteerScript($htmlFile, $pdfFile, $settings);
        $scriptFile    = $this->saveScriptToTempFile($scriptContent);

        try {
            $nodeExecutable = config('pdf.puppeteer.node_executable', 'node');
            $timeout        = config('pdf.puppeteer.script_timeout', 60);

            $command = "timeout {$timeout} {$nodeExecutable} {$scriptFile} 2>&1";

            if (config('pdf.global.debug_mode', false)) {
                Log::debug('Puppeteer command', ['command' => $command]);
            }

            exec($command, $output, $returnCode);

            if (0 !== $returnCode) {
                $errorMessage = implode("\n", $output);

                throw new \RuntimeException("Puppeteer script failed: {$errorMessage}");
            }

            if (config('pdf.global.debug_mode', false)) {
                Log::debug('Puppeteer output', ['output' => $output]);
            }
        } finally {
            $this->addTempFile($scriptFile);
        }
    }

    private function generatePuppeteerScript(string $htmlFile, string $pdfFile, array $settings): string
    {
        $chromeFlags      = implode("', '", $settings['chrome_flags'] ?? []);
        $viewport         = $settings['viewport'] ?? ['width' => 1200, 'height' => 800];
        $margins          = $settings['margins'] ?? ['top' => '5mm', 'right' => '5mm', 'bottom' => '10mm', 'left' => '5mm'];
        $chromeExecutable = config('pdf.puppeteer.chrome_executable', '/usr/bin/google-chrome-stable');

        $footerTemplate = $settings['footer_template'] ?? '';

        if (config('pdf.global.add_footer', true) && !$footerTemplate) {
            $appName        = config('app.name', 'SaasBase');
            $footerTemplate = '<div style="width: 100%; text-align: center; font-size: 8px; color: #666; border-top: 1px solid #E5E7EB; padding-top: 5px;"><span class="date"></span> | ' . $appName . ' | Page <span class="pageNumber"></span> of <span class="totalPages"></span></div>';
        }

        return "
const puppeteer = require('puppeteer');
const fs = require('fs');

(async () => {
    let browser;
    let page;
    
    try {
        console.log('Launching browser...');
        browser = await puppeteer.launch({
            headless: 'new',
            executablePath: '{$chromeExecutable}',
            args: ['{$chromeFlags}'],
            timeout: 30000,
            dumpio: false
        });
        
        console.log('Creating new page...');
        page = await browser.newPage();
        
        // Set a reasonable timeout for page operations
        page.setDefaultTimeout(30000);
        page.setDefaultNavigationTimeout(30000);
        
        console.log('Setting viewport...');
        await page.setViewport({
            width: {$viewport['width']},
            height: {$viewport['height']}
        });
        
        console.log('Reading HTML file...');
        const html = fs.readFileSync('{$htmlFile}', 'utf8');
        
        console.log('Setting page content...');
        await page.setContent(html, { 
            waitUntil: 'domcontentloaded',
            timeout: 30000
        });
        
        " . ($settings['wait_for_selector'] ? "console.log('Waiting for selector...'); await page.waitForSelector('{$settings['wait_for_selector']}', { timeout: 10000 });" : '') . '
        ' . ($settings['wait_for_timeout'] ? "console.log('Waiting for timeout...'); await page.waitForTimeout({$settings['wait_for_timeout']});" : '') . "
        
        console.log('Generating PDF...');
        const pdf = await page.pdf({
            format: '{$settings['format']}',
            landscape: " . ('landscape' === $settings['orientation'] ? 'true' : 'false') . ",
            margin: {
                top: '{$margins['top']}',
                right: '{$margins['right']}',
                bottom: '{$margins['bottom']}',
                left: '{$margins['left']}'
            },
            printBackground: " . ($settings['print_background'] ? 'true' : 'false') . ',
            preferCSSPageSize: ' . ($settings['prefer_css_page_size'] ? 'true' : 'false') . ',
            displayHeaderFooter: ' . ($settings['display_header_footer'] ? 'true' : 'false') . ",
            headerTemplate: '{$settings['header_template']}',
            footerTemplate: '{$footerTemplate}',
            timeout: {$settings['timeout']}
        });
        
        console.log('Writing PDF to file...');
        fs.writeFileSync('{$pdfFile}', pdf);
        
        console.log('PDF generated successfully');
        
    } catch (error) {
        console.error('Puppeteer error:', error);
        throw error;
    } finally {
        if (page) {
            try {
                console.log('Closing page...');
                await page.close();
            } catch (e) {
                console.warn('Error closing page:', e.message);
            }
        }
        if (browser) {
            try {
                console.log('Closing browser...');
                await browser.close();
            } catch (e) {
                console.warn('Error closing browser:', e.message);
            }
        }
    }
})().catch(error => {
    console.error('Fatal Puppeteer error:', error);
    process.exit(1);
});
        ";
    }

    private function saveHtmlToTempFile(string $html): string
    {
        $tempDir  = $this->getTempDirectory();
        $filename = 'invoice_' . Str::random(10) . '.html';
        $filepath = $tempDir . '/' . $filename;

        file_put_contents($filepath, $html);

        return $filepath;
    }

    private function saveScriptToTempFile(string $script): string
    {
        $tempDir  = $this->getTempDirectory();
        $filename = 'puppeteer_script_' . Str::random(10) . '.js';
        $filepath = $tempDir . '/' . $filename;

        file_put_contents($filepath, $script);

        return $filepath;
    }

    private function generateTempPdfPath(): string
    {
        $tempDir  = $this->getTempDirectory();
        $filename = 'invoice_' . Str::random(10) . '.pdf';

        return $tempDir . '/' . $filename;
    }

    private function getTempDirectory(): string
    {
        $tempDir = config('pdf.puppeteer.temp_dir', storage_path('app/temp'));

        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        return $tempDir;
    }

    private function addTempFile(string $filepath): void
    {
        $this->tempFiles[] = $filepath;
    }

    private function convertMpdfToPuppeteerSettings(array $mpdfSettings): array
    {
        $puppeteerSettings = [];

        // Convert format
        if (isset($mpdfSettings['format'])) {
            $puppeteerSettings['format'] = $mpdfSettings['format'];
        }

        // Convert orientation
        if (isset($mpdfSettings['orientation'])) {
            $puppeteerSettings['orientation'] = 'L' === $mpdfSettings['orientation'] ? 'landscape' : 'portrait';
        }

        // Convert margins
        if (isset($mpdfSettings['margins'])) {
            $margins                      = $mpdfSettings['margins'];
            $puppeteerSettings['margins'] = [
                'top'    => ($margins['top'] ?? 5) . 'mm',
                'right'  => ($margins['right'] ?? 5) . 'mm',
                'bottom' => ($margins['bottom'] ?? 10) . 'mm',
                'left'   => ($margins['left'] ?? 5) . 'mm',
            ];
        }

        return $puppeteerSettings;
    }

    private function getDefaultConfig(): array
    {
        return config('pdf.engines.puppeteer.config', [
            'format'      => 'A4',
            'orientation' => 'portrait',
            'margins'     => [
                'top'    => '5mm',
                'right'  => '5mm',
                'bottom' => '10mm',
                'left'   => '5mm',
            ],
            'print_background'      => true,
            'prefer_css_page_size'  => false,
            'display_header_footer' => true,
            'header_template'       => '<div></div>',
            'footer_template'       => '',
            'timeout'               => 30000,
            'wait_for_selector'     => null,
            'wait_for_timeout'      => 0,
            'viewport'              => [
                'width'  => 1200,
                'height' => 800,
            ],
            'chrome_flags' => [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-gpu',
                '--disable-extensions',
                '--disable-plugins',
                '--disable-images',
                '--disable-javascript',
                '--virtual-time-budget=5000',
            ],
        ]);
    }
}
