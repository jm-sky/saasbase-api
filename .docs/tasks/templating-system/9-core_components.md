# Core Templating Components

## 1. **Authorization Policy**

```php
<?php
// App/Domain/Template/Policies/InvoiceTemplatePolicy.php
namespace App\Domain\Template\Policies;

use App\Domain\Auth\Models\User;
use App\Domain\Template\Models\InvoiceTemplate;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoiceTemplatePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true; // Users can view templates in their tenant
    }

    public function view(User $user, InvoiceTemplate $template): bool
    {
        // Users can view system templates or their own templates
        return $template->user_id === null || $template->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true; // All authenticated users can create templates
    }

    public function update(User $user, InvoiceTemplate $template): bool
    {
        // Only template owner can update (system templates cannot be updated)
        return $template->user_id === $user->id;
    }

    public function delete(User $user, InvoiceTemplate $template): bool
    {
        // Only template owner can delete (system templates cannot be deleted)
        return $template->user_id === $user->id;
    }
}
```

## 2. **Template Category Enum**

```php
<?php
// App/Domain/Template/Enums/TemplateCategory.php
namespace App\Domain\Template\Enums;

enum TemplateCategory: string
{
    case INVOICE = 'invoice';
    case QUOTE = 'quote';
    case RECEIPT = 'receipt';
    case ESTIMATE = 'estimate';
    case CREDIT_NOTE = 'credit_note';

    public function label(): string
    {
        return match($this) {
            self::INVOICE => __('templates.categories.invoice'),
            self::QUOTE => __('templates.categories.quote'),
            self::RECEIPT => __('templates.categories.receipt'),
            self::ESTIMATE => __('templates.categories.estimate'),
            self::CREDIT_NOTE => __('templates.categories.credit_note'),
        };
    }
}
```

## 3. **Template DTOs**

```php
<?php
// App/Domain/Template/DTOs/InvoiceTemplateDTO.php
namespace App\Domain\Template\DTOs;

use App\Domain\Template\Enums\TemplateCategory;
use Spatie\LaravelData\Data;

class InvoiceTemplateDTO extends Data
{
    public function __construct(
        public ?string $tenantId,
        public string $name,
        public ?string $description,
        public string $content,
        public array $previewData,
        public bool $isActive,
        public bool $isDefault,
        public ?string $userId,
        public TemplateCategory $category,
        public array $settings,
    ) {}
}
```

```php
<?php
// App/Domain/Template/DTOs/TemplateOptionsDTO.php
namespace App\Domain\Template\DTOs;

use Spatie\LaravelData\Data;

class TemplateOptionsDTO extends Data
{
    public function __construct(
        public string $language = 'en',
        public string $timezone = 'UTC',
        public string $currency = 'USD', 
        public string $accentColor = '#3B82F6',
        public string $secondaryColor = '#6B7280',
        public bool $includeLogo = true,
        public bool $includeSignatures = false,
        public string $dateFormat = 'Y-m-d',
    ) {}
}
```

```php
<?php
// App/Domain/Template/DTOs/TemplateInvoiceDTO.php
namespace App\Domain\Template\DTOs;

use Spatie\LaravelData\Data;

/**
 * DTO representing invoice data for template rendering
 * All monetary values are pre-formatted strings
 */
class TemplateInvoiceDTO extends Data
{
    public function __construct(
        public string $id,
        public string $number,
        public string $issueDate,
        public string $formattedTotalNet,
        public string $formattedTotalTax,
        public string $formattedTotalGross,
        public string $currency,
        public TemplatePartyDTO $seller,
        public TemplatePartyDTO $buyer,
        public TemplateInvoiceBodyDTO $body,
        public TemplatePaymentDTO $payment,
        public TemplateOptionsInvoiceDTO $options,
    ) {}
}
```

```php
<?php
// App/Domain/Template/DTOs/TemplatePartyDTO.php
namespace App\Domain\Template\DTOs;

use Spatie\LaravelData\Data;

class TemplatePartyDTO extends Data
{
    public function __construct(
        public string $name,
        public string $address,
        public ?string $taxId = null,
        public ?string $regon = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $logoUrl = null,
    ) {}
}
```

```php
<?php
// App/Domain/Template/DTOs/TemplateInvoiceBodyDTO.php
namespace App\Domain\Template\DTOs;

use Spatie\LaravelData\Data;

class TemplateInvoiceBodyDTO extends Data
{
    public function __construct(
        /** @var TemplateInvoiceLineDTO[] */
        public array $lines,
        /** @var TemplateVatSummaryDTO[] */
        public array $vatSummary,
        public ?string $notes = null,
    ) {}
}
```

```php
<?php
// App/Domain/Template/DTOs/TemplateInvoiceLineDTO.php
namespace App\Domain\Template\DTOs;

use Spatie\LaravelData\Data;

class TemplateInvoiceLineDTO extends Data
{
    public function __construct(
        public string $description,
        public string $quantity,
        public string $unitPrice,
        public string $formattedNetAmount,
        public string $formattedTaxAmount,
        public string $formattedGrossAmount,
        public string $vatRate,
        public ?string $unit = null,
    ) {}
}
```

```php
<?php
// App/Domain/Template/DTOs/TemplateVatSummaryDTO.php
namespace App\Domain\Template\DTOs;

use Spatie\LaravelData\Data;

class TemplateVatSummaryDTO extends Data
{
    public function __construct(
        public string $vatRate,
        public string $formattedNetAmount,
        public string $formattedTaxAmount,
        public string $formattedGrossAmount,
    ) {}
}
```

```php
<?php
// App/Domain/Template/DTOs/TemplatePaymentDTO.php
namespace App\Domain\Template\DTOs;

use Spatie\LaravelData\Data;

class TemplatePaymentDTO extends Data
{
    public function __construct(
        public string $method,
        public ?string $dueDate = null,
        public ?string $accountNumber = null,
        public ?string $bankName = null,
        public ?string $swift = null,
    ) {}
}
```

```php
<?php
// App/Domain/Template/DTOs/TemplateOptionsInvoiceDTO.php
namespace App\Domain\Template\DTOs;

use Spatie\LaravelData\Data;

class TemplateOptionsInvoiceDTO extends Data
{
    public function __construct(
        public bool $showLogo = true,
        public bool $showSignatures = false,
        public bool $showNotes = true,
        public bool $showVatSummary = true,
        public ?string $logoUrl = null,
        public ?string $signatureUrl = null,
    ) {}
}
```

## 4. **Custom Casts**

```php
<?php
// App/Domain/Template/Casts/TemplatePreviewDataCast.php
namespace App\Domain\Template\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class TemplatePreviewDataCast implements CastsAttributes
{
    public function get(Model $model, string $key, $value, array $attributes): array
    {
        return json_decode($value, true) ?? [];
    }

    public function set(Model $model, string $key, $value, array $attributes): string
    {
        return json_encode($value);
    }
}
```

```php
<?php
// App/Domain/Template/Casts/TemplateSettingsCast.php
namespace App\Domain\Template\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class TemplateSettingsCast implements CastsAttributes
{
    public function get(Model $model, string $key, $value, array $attributes): array
    {
        return json_decode($value, true) ?? [];
    }

    public function set(Model $model, string $key, $value, array $attributes): string
    {
        return json_encode($value);
    }
}
```

## 5. **Core Templating Service**

```php
<?php
// App/Domain/Template/Services/TemplatingService.php
namespace App\Domain\Template\Services;

use App\Domain\Template\DTOs\TemplateOptionsDTO;
use App\Domain\Template\Exceptions\TemplateRenderingException;
use LightnCandy\LightnCandy;

class TemplatingService
{
    private array $compiledTemplateCache = [];

    public function render(string $template, array $data, ?TemplateOptionsDTO $options = null): string
    {
        $options ??= new TemplateOptionsDTO();
        
        try {
            // Compile template with custom helpers
            $compiled = $this->compileTemplate($template);
            
            // Prepare data with options and helpers
            $templateData = $this->prepareTemplateData($data, $options);
            
            // Render
            return $compiled($templateData);
        } catch (\Exception $e) {
            throw new TemplateRenderingException($e->getMessage(), $e);
        }
    }

    private function compileTemplate(string $template): callable
    {
        $templateHash = md5($template);
        
        if (isset($this->compiledTemplateCache[$templateHash])) {
            return $this->compiledTemplateCache[$templateHash];
        }

        $compiled = LightnCandy::compile($template, [
            'flags' => LightnCandy::FLAG_HANDLEBARS | LightnCandy::FLAG_ERROR_EXCEPTION,
            'helpers' => $this->getHelpers(),
            'hbhelpers' => $this->getHandlebarsHelpers(),
        ]);

        $this->compiledTemplateCache[$templateHash] = LightnCandy::prepare($compiled);
        
        return $this->compiledTemplateCache[$templateHash];
    }

    private function prepareTemplateData(array $data, TemplateOptionsDTO $options): array
    {
        return array_merge($data, [
            'options' => $options->toArray(),
            'locale' => $options->language,
            'currency' => $options->currency,
        ]);
    }

    private function getHelpers(): array
    {
        return [
            't' => function ($key, $parameters = []) {
                return __($key, $parameters);
            },
            'formatCurrency' => function ($amount, $currency = null) {
                return number_format((float) $amount, 2) . ' ' . ($currency ?? 'USD');
            },
            'formatDate' => function ($date, $format = 'Y-m-d') {
                if (is_string($date)) {
                    $date = \Carbon\Carbon::parse($date);
                }
                return $date->format($format);
            },
        ];
    }

    private function getHandlebarsHelpers(): array
    {
        return [
            'logoUrl' => function ($cx, $url, $options = []) {
                if (empty($url)) {
                    return '';
                }
                
                $width = $options['width'] ?? '150px';
                $height = $options['height'] ?? 'auto';
                
                return "<img src=\"{$url}\" style=\"width: {$width}; height: {$height};\" alt=\"Logo\" />";
            },
            'signatureUrl' => function ($cx, $url) {
                if (empty($url)) {
                    return '';
                }
                
                return "<img src=\"{$url}\" style=\"max-width: 200px; height: auto;\" alt=\"Signature\" />";
            },
            'pageNumber' => function () {
                return '<script type="text/php">$pdf->text(270, 820, "Page " . $PAGE_NUM . "/" . $PAGE_COUNT, "arial", 10);</script>';
            },
        ];
    }
}
```

## 6. **Invoice Template Service**

```php
<?php
// App/Domain/Template/Services/InvoiceTemplateService.php
namespace App\Domain\Template\Services;

use App\Domain\Template\DTOs\TemplateOptionsDTO;
use App\Domain\Template\Models\InvoiceTemplate;
use Illuminate\Database\Eloquent\Collection;

class InvoiceTemplateService
{
    public function __construct(
        private readonly TemplatingService $templatingService
    ) {}

    public function getAvailableTemplates(?string $userId = null): array
    {
        $systemTemplates = InvoiceTemplate::query()
            ->where('user_id', null)
            ->where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();

        $userTemplates = Collection::make();
        if ($userId) {
            $userTemplates = InvoiceTemplate::query()
                ->where('user_id', $userId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }

        return [
            'system' => $systemTemplates,
            'user' => $userTemplates,
        ];
    }

    public function getTemplate(string $templateName, ?string $userId = null): ?InvoiceTemplate
    {
        return InvoiceTemplate::query()
            ->where('name', $templateName)
            ->where(function ($query) use ($userId) {
                $query->whereNull('user_id') // System templates
                      ->orWhere('user_id', $userId); // User templates
            })
            ->where('is_active', true)
            ->first();
    }

    public function previewTemplate(
        string $content, 
        array $previewData = [], 
        ?TemplateOptionsDTO $options = null
    ): string {
        // Use default preview data if none provided
        if (empty($previewData)) {
            $previewData = $this->getDefaultPreviewData();
        }

        return $this->templatingService->render($content, $previewData, $options);
    }

    private function getDefaultPreviewData(): array
    {
        return [
            'invoice' => [
                'id' => '01234567890123456789012345',
                'number' => 'INV-2024-001',
                'issueDate' => '2024-01-15',
                'formattedTotalNet' => '1,000.00',
                'formattedTotalTax' => '200.00',
                'formattedTotalGross' => '1,200.00',
                'currency' => 'USD',
                'seller' => [
                    'name' => 'Sample Company Ltd.',
                    'address' => '123 Business Street, City, 12345',
                    'taxId' => '1234567890',
                    'email' => 'contact@sample-company.com',
                    'phone' => '+1 (555) 123-4567',
                ],
                'buyer' => [
                    'name' => 'Client Corporation',
                    'address' => '456 Client Avenue, Town, 67890',
                    'taxId' => '0987654321',
                    'email' => 'billing@client-corp.com',
                ],
                'body' => [
                    'lines' => [
                        [
                            'description' => 'Professional Services',
                            'quantity' => '10.00',
                            'unitPrice' => '100.00',
                            'formattedNetAmount' => '1,000.00',
                            'formattedTaxAmount' => '200.00',
                            'formattedGrossAmount' => '1,200.00',
                            'vatRate' => '20%',
                            'unit' => 'hours',
                        ]
                    ],
                    'vatSummary' => [
                        [
                            'vatRate' => '20%',
                            'formattedNetAmount' => '1,000.00',
                            'formattedTaxAmount' => '200.00',
                            'formattedGrossAmount' => '1,200.00',
                        ]
                    ],
                    'notes' => 'Thank you for your business!',
                ],
                'payment' => [
                    'method' => 'Bank Transfer',
                    'dueDate' => '2024-02-15',
                    'accountNumber' => '1234567890123456',
                    'bankName' => 'Sample Bank',
                ],
            ]
        ];
    }
}
```

## 7. **Service Provider**

```php
<?php
// App/Domain/Template/Providers/TemplateServiceProvider.php
namespace App\Domain\Template\Providers;

use App\Domain\Template\Models\InvoiceTemplate;
use App\Domain\Template\Policies\InvoiceTemplatePolicy;
use App\Domain\Template\Services\InvoiceGeneratorService;
use App\Domain\Template\Services\InvoiceTemplateService;
use App\Domain\Template\Services\TemplatingService;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider;

class TemplateServiceProvider extends AuthServiceProvider
{
    protected $policies = [
        InvoiceTemplate::class => InvoiceTemplatePolicy::class,
    ];

    public function register(): void
    {
        $this->app->singleton(TemplatingService::class);
        $this->app->singleton(InvoiceTemplateService::class);
        $this->app->singleton(InvoiceGeneratorService::class);
        $this->app->singleton(InvoiceToTemplateTransformer::class);
        $this->app->singleton(CurrencyFormatterService::class);
        $this->app->singleton(MediaUrlService::class);
    }

    public function boot(): void
    {
        $this->registerPolicies();
    }
} 
