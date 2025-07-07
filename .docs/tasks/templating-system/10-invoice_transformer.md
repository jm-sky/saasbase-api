# Invoice to Template Transformer

## **Invoice to Template Transformer Service**

```php
<?php
// App/Domain/Template/Services/InvoiceToTemplateTransformer.php
namespace App\Domain\Template\Services;

use App\Domain\Invoice\Models\Invoice;
use App\Domain\Template\DTOs\TemplateInvoiceBodyDTO;
use App\Domain\Template\DTOs\TemplateInvoiceDTO;
use App\Domain\Template\DTOs\TemplateInvoiceLineDTO;
use App\Domain\Template\DTOs\TemplateOptionsDTO;
use App\Domain\Template\DTOs\TemplateOptionsInvoiceDTO;
use App\Domain\Template\DTOs\TemplatePartyDTO;
use App\Domain\Template\DTOs\TemplatePaymentDTO;
use App\Domain\Template\DTOs\TemplateVatSummaryDTO;
use Carbon\Carbon;

class InvoiceToTemplateTransformer
{
    public function __construct(
        private readonly CurrencyFormatterService $currencyFormatter,
        private readonly MediaUrlService $mediaUrlService,
    ) {}

    public function transform(Invoice $invoice, TemplateOptionsDTO $options): TemplateInvoiceDTO
    {
        return new TemplateInvoiceDTO(
            id: $invoice->id,
            number: $invoice->number,
            issueDate: $this->formatDate($invoice->issue_date, $options),
            formattedTotalNet: $this->currencyFormatter->format($invoice->total_net, $invoice->currency),
            formattedTotalTax: $this->currencyFormatter->format($invoice->total_tax, $invoice->currency),
            formattedTotalGross: $this->currencyFormatter->format($invoice->total_gross, $invoice->currency),
            currency: $invoice->currency,
            seller: $this->transformParty($invoice->seller, $options),
            buyer: $this->transformParty($invoice->buyer, $options),
            body: $this->transformBody($invoice->body, $invoice->currency),
            payment: $this->transformPayment($invoice->payment, $options),
            options: $this->transformInvoiceOptions($invoice->options),
        );
    }

    private function transformParty(
        \App\Domain\Financial\DTOs\InvoicePartyDTO $party, 
        TemplateOptionsDTO $options
    ): TemplatePartyDTO {
        $logoUrl = null;
        if (isset($party->logo_media_id) && $party->logo_media_id) {
            $logoUrl = $this->mediaUrlService->getSignedUrl($party->logo_media_id);
        }

        return new TemplatePartyDTO(
            name: $party->name,
            address: $this->formatAddress($party),
            taxId: $party->tax_id ?? null,
            regon: $party->regon ?? null,
            email: $party->email ?? null,
            phone: $party->phone ?? null,
            logoUrl: $logoUrl,
        );
    }

    private function formatAddress(\App\Domain\Financial\DTOs\InvoicePartyDTO $party): string
    {
        $parts = array_filter([
            $party->street ?? null,
            $party->city ?? null,
            $party->postal_code ?? null,
            $party->country ?? null,
        ]);

        return implode(', ', $parts);
    }

    private function transformBody(
        \App\Domain\Financial\DTOs\InvoiceBodyDTO $body, 
        string $currency
    ): TemplateInvoiceBodyDTO {
        $lines = [];
        foreach ($body->lines as $line) {
            $lines[] = new TemplateInvoiceLineDTO(
                description: $line->description,
                quantity: number_format($line->quantity, 2),
                unitPrice: $this->currencyFormatter->format($line->unit_price, $currency),
                formattedNetAmount: $this->currencyFormatter->format($line->net_amount, $currency),
                formattedTaxAmount: $this->currencyFormatter->format($line->tax_amount, $currency),
                formattedGrossAmount: $this->currencyFormatter->format($line->gross_amount, $currency),
                vatRate: number_format($line->vat_rate, 1) . '%',
                unit: $line->unit ?? null,
            );
        }

        $vatSummary = [];
        foreach ($body->vat_summary as $vatLine) {
            $vatSummary[] = new TemplateVatSummaryDTO(
                vatRate: number_format($vatLine->vat_rate, 1) . '%',
                formattedNetAmount: $this->currencyFormatter->format($vatLine->net_amount, $currency),
                formattedTaxAmount: $this->currencyFormatter->format($vatLine->tax_amount, $currency),
                formattedGrossAmount: $this->currencyFormatter->format($vatLine->gross_amount, $currency),
            );
        }

        return new TemplateInvoiceBodyDTO(
            lines: $lines,
            vatSummary: $vatSummary,
            notes: $body->notes ?? null,
        );
    }

    private function transformPayment(
        \App\Domain\Financial\DTOs\InvoicePaymentDTO $payment, 
        TemplateOptionsDTO $options
    ): TemplatePaymentDTO {
        $dueDate = null;
        if ($payment->due_date) {
            $dueDate = $this->formatDate($payment->due_date, $options);
        }

        return new TemplatePaymentDTO(
            method: $payment->method,
            dueDate: $dueDate,
            accountNumber: $payment->account_number ?? null,
            bankName: $payment->bank_name ?? null,
            swift: $payment->swift ?? null,
        );
    }

    private function transformInvoiceOptions(
        \App\Domain\Financial\DTOs\InvoiceOptionsDTO $options
    ): TemplateOptionsInvoiceDTO {
        $logoUrl = null;
        if (isset($options->logo_media_id) && $options->logo_media_id) {
            $logoUrl = $this->mediaUrlService->getSignedUrl($options->logo_media_id);
        }

        $signatureUrl = null;
        if (isset($options->signature_media_id) && $options->signature_media_id) {
            $signatureUrl = $this->mediaUrlService->getSignedUrl($options->signature_media_id);
        }

        return new TemplateOptionsInvoiceDTO(
            showLogo: $options->show_logo ?? true,
            showSignatures: $options->show_signatures ?? false,
            showNotes: $options->show_notes ?? true,
            showVatSummary: $options->show_vat_summary ?? true,
            logoUrl: $logoUrl,
            signatureUrl: $signatureUrl,
        );
    }

    private function formatDate($date, TemplateOptionsDTO $options): string
    {
        if (!$date) {
            return '';
        }

        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        // Set timezone if specified
        if ($options->timezone !== 'UTC') {
            $date = $date->setTimezone($options->timezone);
        }

        return $date->format($options->dateFormat);
    }
}
```

## **Currency Formatter Service**

```php
<?php
// App/Domain/Template/Services/CurrencyFormatterService.php
namespace App\Domain\Template\Services;

use Brick\Math\BigDecimal;

class CurrencyFormatterService
{
    /**
     * Format a BigDecimal amount to a localized string with currency symbol
     */
    public function format(BigDecimal $amount, string $currency = 'USD', ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        
        // Convert BigDecimal to float for formatting
        $value = $amount->toFloat();
        
        // Format based on currency and locale
        return $this->formatByCurrency($value, $currency, $locale);
    }

    /**
     * Format by specific currency rules
     */
    private function formatByCurrency(float $value, string $currency, string $locale): string
    {
        $currencySymbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'PLN' => 'zł',
            'UAH' => '₴',
            'RUB' => '₽',
        ];

        $symbol = $currencySymbols[$currency] ?? $currency;
        $formatted = number_format($value, 2, '.', ',');

        // Different positioning based on currency
        return match($currency) {
            'PLN', 'UAH', 'RUB' => $formatted . ' ' . $symbol,
            default => $symbol . $formatted,
        };
    }

    /**
     * Format without currency symbol (for calculations display)
     */
    public function formatNumber(BigDecimal $amount): string
    {
        return number_format($amount->toFloat(), 2, '.', ',');
    }

    /**
     * Format percentage
     */
    public function formatPercentage(BigDecimal $rate): string
    {
        return number_format($rate->toFloat(), 1) . '%';
    }
}
```

## **Media URL Service**

```php
<?php
// App/Domain/Template/Services/MediaUrlService.php
namespace App\Domain\Template\Services;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaUrlService
{
    /**
     * Get signed URL for media file (works with S3 and local storage)
     */
    public function getSignedUrl(string $mediaId, int $expirationMinutes = 60): ?string
    {
        $media = Media::find($mediaId);
        
        if (!$media) {
            return null;
        }

        // For S3 storage, generate temporary signed URL
        if (config('filesystems.default') === 's3') {
            return $media->getTemporaryUrl(
                now()->addMinutes($expirationMinutes)
            );
        }

        // For local storage, return full URL
        return $media->getFullUrl();
    }

    /**
     * Get URL for specific conversion (e.g., thumbnail)
     */
    public function getConversionUrl(string $mediaId, string $conversion = 'thumb'): ?string
    {
        $media = Media::find($mediaId);
        
        if (!$media) {
            return null;
        }

        return $media->getFullUrl($conversion);
    }

    /**
     * Check if media exists and is accessible
     */
    public function mediaExists(string $mediaId): bool
    {
        return Media::where('id', $mediaId)->exists();
    }
}
```

## **Updated Invoice Generator Service**

```php
<?php
// Updated App/Domain/Template/Services/InvoiceGeneratorService.php
namespace App\Domain\Template\Services;

use App\Domain\Invoice\Models\Invoice;
use App\Domain\Template\DTOs\TemplateOptionsDTO;
use App\Domain\Template\Exceptions\TemplateNotFoundException;
use App\Domain\Template\Exceptions\TemplateRenderingException;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceGeneratorService
{
    public function __construct(
        private readonly TemplatingService $templatingService,
        private readonly InvoiceTemplateService $templateService,
        private readonly InvoiceToTemplateTransformer $transformer,
    ) {}
    
    public function generatePdf(
        Invoice $invoice, 
        string $templateName = 'default',
        ?TemplateOptionsDTO $options = null
    ): string {
        $options ??= new TemplateOptionsDTO(
            language: config('app.locale', 'en'),
            timezone: config('app.timezone', 'UTC'),
            currency: $invoice->currency,
        );
        
        // Get template
        $template = $this->templateService->getTemplate($templateName);
        if (!$template) {
            throw new TemplateNotFoundException($templateName);
        }
        
        try {
            // Transform invoice to template DTO
            $templateInvoice = $this->transformer->transform($invoice, $options);
            
            // Render HTML
            $html = $this->templatingService->render(
                $template->content, 
                ['invoice' => $templateInvoice->toArray()], 
                $options
            );
            
            // Generate PDF with page numbers
            return $this->generatePdfFromHtml($html, $options);
        } catch (\Exception $e) {
            throw new TemplateRenderingException($e->getMessage(), $e);
        }
    }
    
    private function generatePdfFromHtml(string $html, TemplateOptionsDTO $options): string
    {
        // Add CSS with custom colors to HTML
        $css = $this->generateCSS($options);
        $htmlWithCss = "<style>{$css}</style>" . $html;
        
        // Add footer with page numbers
        $htmlWithCss .= '
            <script type="text/php">
                if (isset($pdf)) {
                    $pdf->page_script("
                        \$font = \$fontMetrics->get_font(\"arial\", \"normal\");
                        \$size = 10;
                        \$pageText = \"' . __('invoices.page') . ' \" . \$PAGE_NUM . \"/\" . \$PAGE_COUNT;
                        \$pdf->text(270, 820, \$pageText, \$font, \$size);
                    ");
                }
            </script>';
        
        $pdf = Pdf::loadHTML($htmlWithCss)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'isPhpEnabled' => true,
                'isRemoteEnabled' => false,
                'defaultFont' => 'arial',
            ]);
        
        return $pdf->output();
    }
    
    private function generateCSS(TemplateOptionsDTO $options): string
    {
        return "
        :root {
            --accent-color: {$options->accentColor};
            --secondary-color: {$options->secondaryColor};
        }
        
        .invoice-container { 
            max-width: 800px; 
            margin: 0 auto; 
            font-family: Arial, sans-serif; 
        }
        
        .accent-bg { background-color: {$options->accentColor}; }
        .accent-text { color: {$options->accentColor}; }
        .accent-border { border-color: {$options->accentColor}; }
        .secondary-text { color: {$options->secondaryColor}; }
        
        /* Tailwind-like utilities */
        .w-full { width: 100%; }
        .w-1\\/2 { width: 50%; }
        .flex { display: flex; }
        .justify-between { justify-content: space-between; }
        .items-center { align-items: center; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .font-semibold { font-weight: 600; }
        .text-lg { font-size: 18px; }
        .text-xl { font-size: 20px; }
        .text-2xl { font-size: 24px; }
        .text-3xl { font-size: 30px; }
        .mb-2 { margin-bottom: 8px; }
        .mb-4 { margin-bottom: 16px; }
        .mb-6 { margin-bottom: 24px; }
        .mb-8 { margin-bottom: 32px; }
        .mt-4 { margin-top: 16px; }
        .mt-6 { margin-top: 24px; }
        .mt-8 { margin-top: 32px; }
        .p-4 { padding: 16px; }
        .py-2 { padding-top: 8px; padding-bottom: 8px; }
        .py-3 { padding-top: 12px; padding-bottom: 12px; }
        .px-4 { padding-left: 16px; padding-right: 16px; }
        .border { border: 1px solid #d1d5db; }
        .border-b { border-bottom: 1px solid #d1d5db; }
        .border-gray-300 { border-color: #d1d5db; }
        .bg-gray-50 { background-color: #f9fafb; }
        .text-gray-600 { color: #4b5563; }
        .text-gray-900 { color: #111827; }
        
        .invoice-table { 
            width: 100%; 
            border-collapse: collapse; 
        }
        .invoice-table th, 
        .invoice-table td { 
            border: 1px solid #e5e7eb; 
            padding: 8px; 
            text-align: left; 
        }
        .invoice-table th { 
            background-color: {$options->accentColor}; 
            color: white; 
            font-weight: bold; 
        }
        ";
    }
}
``` 
