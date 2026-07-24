<?php

namespace App\Domain\Template\Services\PdfEngines;

use App\Domain\Template\Contracts\PdfEngineInterface;
use App\Domain\Template\Models\InvoiceTemplate;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PuppeteerEngine implements PdfEngineInterface
{
    private const ALLOWED_FORMATS = ['A0', 'A1', 'A2', 'A3', 'A4', 'A5', 'A6', 'Letter', 'Legal', 'Tabloid', 'Ledger'];

    private const MARGIN_PATTERN = '/^\d+(\.\d+)?(mm|cm|in|px|pt)$/';

    private array $config;

    private array $tempFiles = [];

    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->getDefaultConfig(), $config);
    }

    public function generatePdf(string $html, array $settings = []): string
    {
        $htmlFile = $this->saveHtmlToTempFile($html);
        $pdfFile = $this->generateTempPdfPath();

        try {
            $this->runPuppeteerScript($htmlFile, $pdfFile, array_merge($this->config, $settings));

            if (! file_exists($pdfFile)) {
                throw new \RuntimeException('PDF file was not generated');
            }

            $pdfContent = file_get_contents($pdfFile);

            if ($pdfContent === false) {
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
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Content-Length' => strlen($pdfContent),
        ]);
    }

    public function streamPdf(string $html, string $filename, array $settings = []): \Symfony\Component\HttpFoundation\Response
    {
        $pdfContent = $this->generatePdf($html, $settings);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
            'Content-Length' => strlen($pdfContent),
        ]);
    }

    public function getName(): string
    {
        return 'puppeteer';
    }

    public function isAvailable(): bool
    {
        $nodeExecutable = config('pdf.puppeteer.node_executable', 'node');
        $npmExecutable = config('pdf.puppeteer.npm_executable', 'npm');

        // Check if Node.js is available
        exec("which {$nodeExecutable}", $output, $returnCode);

        if ($returnCode !== 0) {
            return false;
        }

        // Check if npm is available
        exec("which {$npmExecutable}", $output, $returnCode);

        if ($returnCode !== 0) {
            return false;
        }

        // Check if Puppeteer package is actually installed
        exec("{$nodeExecutable} -e \"require('puppeteer')\" 2>/dev/null", $output, $returnCode);

        if ($returnCode !== 0) {
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
            $this->config = array_merge($this->config, $templateSettings);
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

    /**
     * Every value here can originate from InvoiceTemplate.settings, which is
     * editable by any tenant user with invoice_templates.manage — a field
     * with no schema beyond "is an array". The generated script must never
     * interpolate these values directly into JS source (that was an RCE:
     * a crafted settings.format/margins value could break out of the string
     * literal and inject arbitrary Node.js code). Instead everything is
     * written to a JSON file and read back via JSON.parse(), which cannot
     * be escaped out of regardless of content, plus each value is
     * type/range-validated here as defense in depth.
     */
    private function runPuppeteerScript(string $htmlFile, string $pdfFile, array $settings): void
    {
        $resolved = $this->resolveScriptSettings($htmlFile, $pdfFile, $settings);
        $settingsFile = $this->saveSettingsToTempFile($resolved);
        $scriptContent = $this->generatePuppeteerScript($settingsFile);
        $scriptFile = $this->saveScriptToTempFile($scriptContent);

        try {
            $nodeExecutable = config('pdf.puppeteer.node_executable', 'node');
            $timeout = config('pdf.puppeteer.script_timeout', 60);

            $command = "timeout {$timeout} {$nodeExecutable} {$scriptFile} 2>&1";

            if (config('pdf.global.debug_mode', false)) {
                Log::debug('Puppeteer command', ['command' => $command]);
            }

            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                $errorMessage = implode("\n", $output);

                throw new \RuntimeException("Puppeteer script failed: {$errorMessage}");
            }

            if (config('pdf.global.debug_mode', false)) {
                Log::debug('Puppeteer output', ['output' => $output]);
            }
        } finally {
            $this->addTempFile($scriptFile);
            $this->addTempFile($settingsFile);
        }
    }

    /**
     * Normalizes/validates every field that ends up in the generated
     * script's settings JSON. Unknown/invalid values fall back to safe
     * defaults rather than being passed through — this is intentionally
     * stricter than "just JSON-encode it", since e.g. an unbounded timeout
     * or oversized viewport is still an abuse vector even once injection
     * itself is closed off.
     */
    private function resolveScriptSettings(string $htmlFile, string $pdfFile, array $settings): array
    {
        $margins = is_array($settings['margins'] ?? null) ? $settings['margins'] : [];
        $viewport = is_array($settings['viewport'] ?? null) ? $settings['viewport'] : [];
        $format = (string) ($settings['format'] ?? 'A4');
        $footerTemplate = (string) ($settings['footer_template'] ?? '');

        if (config('pdf.global.add_footer', true) && $footerTemplate === '') {
            $appName = config('app.name', 'SaasBase');
            $footerTemplate = '<div style="width: 100%; text-align: center; font-size: 8px; color: #666; border-top: 1px solid #E5E7EB; padding-top: 5px;"><span class="date"></span> | '.$appName.' | Page <span class="pageNumber"></span> of <span class="totalPages"></span></div>';
        }

        return [
            'chromeExecutable' => (string) config('pdf.puppeteer.chrome_executable', '/usr/bin/google-chrome-stable'),
            'chromeFlags' => array_values(array_filter((array) ($settings['chrome_flags'] ?? []), 'is_string')),
            'htmlFile' => $htmlFile,
            'pdfFile' => $pdfFile,
            'viewport' => [
                'width' => $this->clampInt($viewport['width'] ?? 1200, 200, 4000),
                'height' => $this->clampInt($viewport['height'] ?? 800, 200, 4000),
            ],
            'format' => \in_array($format, self::ALLOWED_FORMATS, true) ? $format : 'A4',
            'landscape' => 'landscape' === ($settings['orientation'] ?? 'portrait'),
            'margins' => [
                'top' => $this->resolveMargin($margins['top'] ?? null, '5mm'),
                'right' => $this->resolveMargin($margins['right'] ?? null, '5mm'),
                'bottom' => $this->resolveMargin($margins['bottom'] ?? null, '10mm'),
                'left' => $this->resolveMargin($margins['left'] ?? null, '5mm'),
            ],
            'printBackground' => (bool) ($settings['print_background'] ?? true),
            'preferCssPageSize' => (bool) ($settings['prefer_css_page_size'] ?? false),
            'displayHeaderFooter' => (bool) ($settings['display_header_footer'] ?? true),
            'headerTemplate' => (string) ($settings['header_template'] ?? '<div></div>'),
            'footerTemplate' => $footerTemplate,
            'timeout' => $this->clampInt($settings['timeout'] ?? 30000, 1000, 120000),
            'waitForSelector' => \is_string($settings['wait_for_selector'] ?? null) ? $settings['wait_for_selector'] : null,
            'waitForTimeout' => $this->clampInt($settings['wait_for_timeout'] ?? 0, 0, 30000),
        ];
    }

    private function resolveMargin(mixed $value, string $default): string
    {
        if (\is_string($value) && preg_match(self::MARGIN_PATTERN, $value)) {
            return $value;
        }

        return $default;
    }

    private function clampInt(mixed $value, int $min, int $max): int
    {
        return max($min, min($max, (int) $value));
    }

    private function generatePuppeteerScript(string $settingsFile): string
    {
        return "
const puppeteer = require('puppeteer');
const fs = require('fs');

(async () => {
    let browser;
    let page;

    try {
        const settings = JSON.parse(fs.readFileSync('{$settingsFile}', 'utf8'));

        console.log('Launching browser...');
        browser = await puppeteer.launch({
            headless: 'new',
            executablePath: settings.chromeExecutable,
            args: settings.chromeFlags,
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
            width: settings.viewport.width,
            height: settings.viewport.height
        });

        console.log('Reading HTML file...');
        const html = fs.readFileSync(settings.htmlFile, 'utf8');

        console.log('Setting page content...');
        await page.setContent(html, {
            waitUntil: 'domcontentloaded',
            timeout: 30000
        });

        if (settings.waitForSelector) {
            console.log('Waiting for selector...');
            await page.waitForSelector(settings.waitForSelector, { timeout: 10000 });
        }
        if (settings.waitForTimeout) {
            console.log('Waiting for timeout...');
            await page.waitForTimeout(settings.waitForTimeout);
        }

        console.log('Generating PDF...');
        const pdf = await page.pdf({
            format: settings.format,
            landscape: settings.landscape,
            margin: settings.margins,
            printBackground: settings.printBackground,
            preferCSSPageSize: settings.preferCssPageSize,
            displayHeaderFooter: settings.displayHeaderFooter,
            headerTemplate: settings.headerTemplate,
            footerTemplate: settings.footerTemplate,
            timeout: settings.timeout
        });

        console.log('Writing PDF to file...');
        fs.writeFileSync(settings.pdfFile, pdf);

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
        $tempDir = $this->getTempDirectory();
        $filename = 'invoice_'.Str::random(10).'.html';
        $filepath = $tempDir.'/'.$filename;

        file_put_contents($filepath, $html);

        return $filepath;
    }

    private function saveScriptToTempFile(string $script): string
    {
        $tempDir = $this->getTempDirectory();
        $filename = 'puppeteer_script_'.Str::random(10).'.js';
        $filepath = $tempDir.'/'.$filename;

        file_put_contents($filepath, $script);

        return $filepath;
    }

    private function saveSettingsToTempFile(array $settings): string
    {
        $tempDir = $this->getTempDirectory();
        $filename = 'puppeteer_settings_'.Str::random(10).'.json';
        $filepath = $tempDir.'/'.$filename;

        file_put_contents($filepath, json_encode($settings, \JSON_THROW_ON_ERROR));

        return $filepath;
    }

    private function generateTempPdfPath(): string
    {
        $tempDir = $this->getTempDirectory();
        $filename = 'invoice_'.Str::random(10).'.pdf';

        return $tempDir.'/'.$filename;
    }

    private function getTempDirectory(): string
    {
        $tempDir = config('pdf.puppeteer.temp_dir', storage_path('app/temp'));

        if (! is_dir($tempDir)) {
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
        if (isset($mpdfSettings['format']) && \in_array($mpdfSettings['format'], self::ALLOWED_FORMATS, true)) {
            $puppeteerSettings['format'] = $mpdfSettings['format'];
        }

        // Convert orientation
        if (isset($mpdfSettings['orientation'])) {
            $puppeteerSettings['orientation'] = $mpdfSettings['orientation'] === 'L' ? 'landscape' : 'portrait';
        }

        // Convert margins
        if (isset($mpdfSettings['margins']) && \is_array($mpdfSettings['margins'])) {
            $margins = $mpdfSettings['margins'];

            $puppeteerSettings['margins'] = [
                'top' => $this->resolveMargin($this->numericMargin($margins['top'] ?? null), '5mm'),
                'right' => $this->resolveMargin($this->numericMargin($margins['right'] ?? null), '5mm'),
                'bottom' => $this->resolveMargin($this->numericMargin($margins['bottom'] ?? null), '10mm'),
                'left' => $this->resolveMargin($this->numericMargin($margins['left'] ?? null), '5mm'),
            ];
        }

        return $puppeteerSettings;
    }

    private function numericMargin(mixed $value): ?string
    {
        if (! is_numeric($value)) {
            return null;
        }

        return $value.'mm';
    }

    private function getDefaultConfig(): array
    {
        return config('pdf.engines.puppeteer.config', [
            'format' => 'A4',
            'orientation' => 'portrait',
            'margins' => [
                'top' => '5mm',
                'right' => '5mm',
                'bottom' => '10mm',
                'left' => '5mm',
            ],
            'print_background' => true,
            'prefer_css_page_size' => false,
            'display_header_footer' => true,
            'header_template' => '<div></div>',
            'footer_template' => '',
            'timeout' => 30000,
            'wait_for_selector' => null,
            'wait_for_timeout' => 0,
            'viewport' => [
                'width' => 1200,
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
