```php
<?php

// tests/Feature/Domain/Template/InvoiceTemplateTest.php
namespace Tests\Feature\Domain\Template;

use App\Domain\Auth\Models\User;
use App\Domain\Template\Enums\TemplateCategory;
use App\Domain\Template\Models\InvoiceTemplate;
use App\Domain\Template\Services\InvoiceTemplateService;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTemplateTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Tenant $tenant;
    private InvoiceTemplateService $templateService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->templateService = app(InvoiceTemplateService::class);
        
        $this->actingAs($this->user);
    }

    public function test_user_can_create_template(): void
    {
        $templateData = [
            'name' => 'test-template',
            'description' => 'Test description',
            'content' => '<div>{{invoice.number}}</div>',
            'category' => 'invoice'
        ];

        $template = $this->templateService->createTemplate($templateData, $this->user->id);

        expect($template)->toBeInstanceOf(InvoiceTemplate::class);
        expect($template->name)->toBe('test-template');
        expect($template->user_id)->toBe($this->user->id);
        expect($template->tenant_id)->toBe($this->tenant->id);
    }

    public function test_user_cannot_access_other_tenant_templates(): void
    {
        $otherTenant = Tenant::factory()->create();
        $otherUser = User::factory()->create(['tenant_id' => $otherTenant->id]);
        
        // Create template in other tenant
        InvoiceTemplate::factory()
            ->userTemplate($otherUser->id)
            ->create([
                'tenant_id' => $otherTenant->id,
                'name' => 'other-template'
            ]);

        // Should not find template from other tenant
        $template = $this->templateService->getTemplate('other-template', $this->user->id);
        expect($template)->toBeNull();
    }

    public function test_system_templates_are_accessible_to_all_tenants(): void
    {
        // Create system template for this tenant
        InvoiceTemplate::factory()
            ->system()
            ->create([
                'tenant_id' => $this->tenant->id,
                'name' => 'system-template'
            ]);

        $template = $this->templateService->getTemplate('system-template');
        expect($template)->not->toBeNull();
        expect($template->user_id)->toBeNull();
    }

    public function test_template_name_must_be_unique_per_tenant_user(): void
    {
        // Create first template
        $this->templateService->createTemplate([
            'name' => 'duplicate-name',
            'content' => '<div>Test</div>',
        ], $this->user->id);

        // Try to create duplicate - should fail
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        
        $this->templateService->createTemplate([
            'name' => 'duplicate-name',
            'content' => '<div>Test 2</div>',
        ], $this->user->id);
    }

    public function test_user_can_update_own_template(): void
    {
        $template = InvoiceTemplate::factory()
            ->userTemplate($this->user->id)
            ->create(['name' => 'original-name']);

        $updatedTemplate = $this->templateService->updateTemplate(
            $template->id,
            ['name' => 'updated-name'],
            $this->user->id
        );

        expect($updatedTemplate->name)->toBe('updated-name');
    }

    public function test_user_cannot_update_other_user_template(): void
    {
        $otherUser = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $template = InvoiceTemplate::factory()
            ->userTemplate($otherUser->id)
            ->create();

        $this->expectException(\UnauthorizedException::class);
        
        $this->templateService->updateTemplate(
            $template->id,
            ['name' => 'hacked-name'],
            $this->user->id
        );
    }
}

// tests/Feature/Domain/Template/InvoiceGenerationTest.php
namespace Tests\Feature\Domain\Template;

use App\Domain\Financial\DTOs\InvoiceDTO;
use App\Domain\Template\DTOs\TemplateOptionsDTO;
use App\Domain\Template\Services\InvoiceGeneratorService;
use App\Domain\Template\Models\InvoiceTemplate;
use Tests\TestCase;

class InvoiceGenerationTest extends TestCase
{
    public function test_can_generate_pdf_from_template(): void
    {
        $template = InvoiceTemplate::factory()
            ->system()
            ->create([
                'name' => 'test-template',
                'content' => '<div>Invoice: {{invoice.number}}</div>'
            ]);

        $invoiceDTO = $this->createMockInvoiceDTO();
        $options = new TemplateOptionsDTO(language: 'en');

        $generator = app(InvoiceGeneratorService::class);
        $pdfContent = $generator->generatePdf($invoiceDTO, 'test-template', $options);

        expect($pdfContent)->toBeString();
        expect(str_starts_with($pdfContent, '%PDF'))->toBeTrue();
    }

    private function createMockInvoiceDTO(): InvoiceDTO
    {
        // Create a mock InvoiceDTO - adapt this to your actual DTO structure
        return new InvoiceDTO(
            id: 'test-invoice-id',
            tenant_id: 'test-tenant',
            // ... other required properties
        );
    }
}

// tests/Unit/Domain/Template/CurrencyFormatterServiceTest.php
namespace Tests\Unit\Domain\Template;

use App\Domain\Template\Services\CurrencyFormatterService;
use Brick\Math\BigDecimal;
use Tests\TestCase;

class CurrencyFormatterServiceTest extends TestCase
{
    private CurrencyFormatterService $formatter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->formatter = new CurrencyFormatterService();
    }

    public function test_formats_pln_currency_correctly(): void
    {
        $amount = BigDecimal::of('1234.56');
        $formatted = $this->formatter->format($amount, 'PLN');
        
        expect($formatted)->toBe('1,234.56 zł');
    }

    public function test_formats_usd_currency_correctly(): void
    {
        $amount = BigDecimal::of('1234.56');
        $formatted = $this->formatter->format($amount, 'USD');
        
        expect($formatted)->toBe('$1,234.56');
    }

    public function test_gets_correct_currency_symbol(): void
    {
        expect($this->formatter->getSymbol('PLN'))->toBe('zł');
        expect($this->formatter->getSymbol('USD'))->toBe('$');
        expect($this->formatter->getSymbol('EUR'))->toBe('€');
        expect($this->formatter->getSymbol('UAH'))->toBe('₴');
        expect($this->formatter->getSymbol('RUB'))->toBe('₽');
    }
}

// config/template.php - Configuration file
<?php

return [
    /**
     * Default template options
     */
    'defaults' => [
        'language' => env('TEMPLATE_DEFAULT_LANGUAGE', 'en'),
        'timezone' => env('TEMPLATE_DEFAULT_TIMEZONE', 'UTC'),
        'currency' => env('TEMPLATE_DEFAULT_CURRENCY', 'USD'),
        'accent_color' => env('TEMPLATE_DEFAULT_ACCENT_COLOR', '#3B82F6'),
        'secondary_color' => env('TEMPLATE_DEFAULT_SECONDARY_COLOR', '#6B7280'),
        'date_format' => env('TEMPLATE_DEFAULT_DATE_FORMAT', 'Y-m-d'),
        'include_logo' => env('TEMPLATE_DEFAULT_INCLUDE_LOGO', true),
        'include_signatures' => env('TEMPLATE_DEFAULT_INCLUDE_SIGNATURES', false),
    ],

    /**
     * Supported languages for templates
     */
    'supported_languages' => [
        'en' => 'English',
        'pl' => 'Polski',
        'uk' => 'Українська',
        'ru' => 'Русский',
    ],

    /**
     * Supported currencies
     */
    'supported_currencies' => [
        'PLN' => ['symbol' => 'zł', 'decimals' => 2, 'before' => false],
        'USD' => ['symbol' => '$', 'decimals' => 2, 'before' => true],
        'EUR' => ['symbol' => '€', 'decimals' => 2, 'before' => false],
        'UAH' => ['symbol' => '₴', 'decimals' => 2, 'before' => false],
        'RUB' => ['symbol' => '₽', 'decimals' => 2, 'before' => false],
    ],

    /**
     * PDF generation settings
     */
    'pdf' => [
        'format' => 'A4',
        'margin_top' => 20,
        'margin_bottom' => 30,
        'margin_left' => 15,
        'margin_right' => 15,
        'default_font' => 'arial',
    ],

    /**
     * Template compilation cache settings
     */
    'cache' => [
        'ttl' => env('TEMPLATE_CACHE_TTL', 3600), // 1 hour
        'prefix' => 'template_compiled_',
    ],

    /**
     * Security settings
     */
    'security' => [
        'max_template_size' => env('TEMPLATE_MAX_SIZE', 1024 * 1024), // 1MB
        'allowed_helpers' => [
            't',
            'logoUrl',
            'signatureUrl',
            'pageNumber',
            'totalPages',
            'eq',
            'ne',
            'gt',
            'lt',
            'gte',
            'lte',
        ],
    ],
];

// App/Domain/Template/Services/TemplateConfigurationService.php
namespace App\Domain\Template\Services;

class TemplateConfigurationService
{
    /**
     * Get template configuration for tenant
     * 
     * @return array<string, mixed>
     */
    public function getConfiguration(): array
    {
        return [
            'defaults' => config('template.defaults'),
            'languages' => config('template.supported_languages'),
            'currencies' => config('template.supported_currencies'),
            'colorSchemes' => $this->getColorSchemes(),
            'dateFormats' => $this->getDateFormats(),
        ];
    }

    /**
     * @return array<string, array{name: string, accent: string, secondary: string}>
     */
    private function getColorSchemes(): array
    {
        return [
            'blue' => ['name' => 'Professional Blue', 'accent' => '#3B82F6', 'secondary' => '#6B7280'],
            'green' => ['name' => 'Business Green', 'accent' => '#10B981', 'secondary' => '#6B7280'],
            'purple' => ['name' => 'Creative Purple', 'accent' => '#8B5CF6', 'secondary' => '#6B7280'],
            'red' => ['name' => 'Bold Red', 'accent' => '#EF4444', 'secondary' => '#6B7280'],
            'orange' => ['name' => 'Energetic Orange', 'accent' => '#F97316', 'secondary' => '#6B7280'],
            'teal' => ['name' => 'Modern Teal', 'accent' => '#14B8A6', 'secondary' => '#6B7280'],
            'gray' => ['name' => 'Classic Gray', 'accent' => '#374151', 'secondary' => '#9CA3AF'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function getDateFormats(): array
    {
        return [
            'Y-m-d' => '2024-07-06',
            'd/m/Y' => '06/07/2024',
            'm/d/Y' => '07/06/2024',
            'd.m.Y' => '06.07.2024',
            'F j, Y' => 'July 6, 2024',
            'j F Y' => '6 July 2024',
        ];
    }
}

// App/Http/Controllers/Api/Template/TemplateConfigurationController.php
namespace App\Http\Controllers\Api\Template;

use App\Domain\Template\Services\TemplateConfigurationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class TemplateConfigurationController extends Controller
{
    public function __construct(
        private readonly TemplateConfigurationService $configService,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json($this->configService->getConfiguration());
    }
}

// App/Console/Commands/GenerateInvoiceTemplatesCommand.php
namespace App\Console\Commands;

use App\Domain\Template\Services\InvoiceTemplateService;
use Illuminate\Console\Command;

class GenerateInvoiceTemplatesCommand extends Command
{
    protected $signature = 'templates:generate-invoice 
                           {name : Template name}
                           {--user= : User ID for user template}
                           {--description= : Template description}';

    protected $description = 'Generate a new invoice template from CLI';

    public function handle(InvoiceTemplateService $templateService): int
    {
        $name = $this->argument('name');
        $userId = $this->option('user');
        $description = $this->option('description');

        $content = $this->ask('Enter template content (Handlebars syntax)');

        try {
            $template = $templateService->createTemplate([
                'name' => $name,
                'description' => $description,
                'content' => $content,
                'category' => 'invoice',
            ], $userId);

            $this->info("Template '{$template->name}' created successfully with ID: {$template->id}");
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to create template: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}

// App/Domain/Template/Observers/InvoiceTemplateObserver.php
namespace App\Domain\Template\Observers;

use App\Domain\Template\Models\InvoiceTemplate;
use Illuminate\Support\Facades\Cache;

class InvoiceTemplateObserver
{
    public function created(InvoiceTemplate $template): void
    {
        $this->clearRelatedCache($template);
    }

    public function updated(InvoiceTemplate $template): void
    {
        $this->clearRelatedCache($template);
    }

    public function deleted(InvoiceTemplate $template): void
    {
        $this->clearRelatedCache($template);
    }

    private function clearRelatedCache(InvoiceTemplate $template): void
    {
        // Clear template cache
        Cache::forget("invoice_template_{$template->name}_{$template->user_id}");
        
        // Clear compiled template cache
        Cache::forget("template_compiled_" . md5($template->content));
        
        // Clear tenant templates list cache
        Cache::forget("templates_list_{$template->tenant_id}");
    }
}

// Register observer in App/Providers/TemplateServiceProvider.php
namespace App\Providers;

use App\Domain\Template\Models\InvoiceTemplate;
use App\Domain\Template\Observers\InvoiceTemplateObserver;
use App\Domain\Template\Services\CurrencyFormatterService;
use App\Domain\Template\Services\InvoiceGeneratorService;
use App\Domain\Template\Services\InvoiceTemplateService;
use App\Domain\Template\Services\InvoiceToTemplateTransformer;
use App\Domain\Template\Services\MediaUrlService;
use App\Domain\Template\Services\TemplateConfigurationService;
use App\Domain\Template\Services\TemplatingService;
use Illuminate\Support\ServiceProvider;

class TemplateServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    public array $singletons = [
        CurrencyFormatterService::class => CurrencyFormatterService::class,
        MediaUrlService::class => MediaUrlService::class,
        TemplatingService::class => TemplatingService::class,
        InvoiceTemplateService::class => InvoiceTemplateService::class,
        InvoiceToTemplateTransformer::class => InvoiceToTemplateTransformer::class,
        InvoiceGeneratorService::class => InvoiceGeneratorService::class,
        TemplateConfigurationService::class => TemplateConfigurationService::class,
    ];

    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(__DIR__ . '/../../config/template.php', 'template');
    }

    public function boot(): void
    {
        // Register observers
        InvoiceTemplate::observe(InvoiceTemplateObserver::class);

        // Publish config
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/template.php' => config_path('template.php'),
            ], 'template-config');
        }
    }
}

// Additional route for configuration
// Add to routes/api.php
/*
Route::prefix('templates')->name('templates.')->group(function () {
    Route::get('/configuration', [TemplateConfigurationController::class, 'index'])->name('config');
    // ... other template routes
});
*/

// Middleware for template size validation
// App/Http/Middleware/ValidateTemplateSize.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ValidateTemplateSize
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->has('content')) {
            $maxSize = config('template.security.max_template_size', 1048576); // 1MB default
            $contentSize = strlen($request->input('content'));
            
            if ($contentSize > $maxSize) {
                return response()->json([
                    'error' => 'Template content exceeds maximum allowed size'
                ], 413);
            }
        }

        return $next($request);
    }
}

// Artisan command to clear template cache
// App/Console/Commands/ClearTemplateCacheCommand.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearTemplateCacheCommand extends Command
{
    protected $signature = 'templates:clear-cache {--tenant= : Specific tenant ID}';
    protected $description = 'Clear template compilation cache';

    public function handle(): int
    {
        $tenantId = $this->option('tenant');
        
        if ($tenantId) {
            $this->clearTenantCache($tenantId);
            $this->info("Cleared template cache for tenant: {$tenantId}");
        } else {
            $this->clearAllTemplateCache();
            $this->info('Cleared all template cache');
        }

        return self::SUCCESS;
    }

    private function clearTenantCache(string $tenantId): void
    {
        // Implementation depends on your cache structure
        Cache::forget("templates_list_{$tenantId}");
    }

    private function clearAllTemplateCache(): void
    {
        $keys = Cache::getRedis()->keys(config('template.cache.prefix') . '*');
        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }
}
```
