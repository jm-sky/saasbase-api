<?php

// App/Domain/Template/Services/InvoiceGeneratorService.php
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
        <style>
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
        </style>";
    }
}

// App/Domain/Template/Exceptions/TemplateNotFoundException.php
namespace App\Domain\Template\Exceptions;

use Exception;

class TemplateNotFoundException extends Exception
{
    public function __construct(string $templateName)
    {
        parent::__construct("Template '{$templateName}' not found");
    }
}

// App/Domain/Template/Exceptions/TemplateRenderingException.php
namespace App\Domain\Template\Exceptions;

use Exception;
use Throwable;

class TemplateRenderingException extends Exception
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct("Template rendering failed: {$message}", 0, $previous);
    }
}

// App/Domain/Template/Controllers/InvoiceTemplateController.php
namespace App\Domain\Template\Controllers;

use App\Domain\Common\Filters\AdvancedFilter;
use App\Domain\Common\Filters\ComboSearchFilter;
use App\Domain\Common\Filters\DateRangeFilter;
use App\Domain\Common\Traits\HasIndexQuery;
use App\Domain\Template\DTOs\InvoiceTemplateDTO;
use App\Domain\Template\Models\InvoiceTemplate;
use App\Domain\Template\Requests\CreateInvoiceTemplateRequest;
use App\Domain\Template\Requests\UpdateInvoiceTemplateRequest;
use App\Domain\Template\Requests\PreviewTemplateRequest;
use App\Domain\Template\Resources\InvoiceTemplateResource;
use App\Domain\Template\Services\InvoiceTemplateService;
use App\Domain\Template\Services\TemplatingService;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\AllowedFilter;
use Symfony\Component\HttpFoundation\Response;

class InvoiceTemplateController extends Controller
{
    use HasIndexQuery;
    use AuthorizesRequests;

    protected int $defaultPerPage = 15;

    public function __construct(
        private readonly InvoiceTemplateService $templateService,
        private readonly TemplatingService $templatingService,
    ) {
        $this->modelClass = InvoiceTemplate::class;

        $this->filters = [
            AllowedFilter::custom('search', new ComboSearchFilter(['name', 'description'])),
            AllowedFilter::custom('name', new AdvancedFilter()),
            AllowedFilter::custom('category', new AdvancedFilter()),
            AllowedFilter::exact('isActive', 'is_active'),
            AllowedFilter::exact('isDefault', 'is_default'),
            AllowedFilter::custom('createdAt', new DateRangeFilter('created_at')),
        ];

        $this->sorts = [
            'name',
            'category',
            'isActive' => 'is_active',
            'isDefault' => 'is_default',
            'createdAt' => 'created_at',
        ];

        $this->defaultSort = '-created_at';
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $templates = $this->getIndexPaginator($request);

        return InvoiceTemplateResource::collection($templates['data'])
            ->additional(['meta' => $templates['meta']]);
    }

    public function store(CreateInvoiceTemplateRequest $request): InvoiceTemplateResource
    {
        $dto = InvoiceTemplateDTO::from($request->validated());
        $template = InvoiceTemplate::create((array) $dto);

        return new InvoiceTemplateResource($template);
    }

    public function show(InvoiceTemplate $invoiceTemplate): InvoiceTemplateResource
    {
        $this->authorize('view', $invoiceTemplate);

        return new InvoiceTemplateResource($invoiceTemplate);
    }

    public function update(UpdateInvoiceTemplateRequest $request, InvoiceTemplate $invoiceTemplate): InvoiceTemplateResource
    {
        $this->authorize('update', $invoiceTemplate);

        $invoiceTemplate->update($request->validated());

        return new InvoiceTemplateResource($invoiceTemplate);
    }

    public function destroy(InvoiceTemplate $invoiceTemplate): JsonResponse
    {
        $this->authorize('delete', $invoiceTemplate);

        $invoiceTemplate->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function preview(PreviewTemplateRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            
            $options = new TemplateOptionsDTO(
                language: $validated['options']['language'] ?? config('app.locale', 'en'),
                timezone: $validated['options']['timezone'] ?? config('app.timezone', 'UTC'),
                accentColor: $validated['options']['accentColor'] ?? '#3B82F6',
                secondaryColor: $validated['options']['secondaryColor'] ?? '#6B7280',
                includeLogo: $validated['options']['includeLogo'] ?? true,
                includeSignatures: $validated['options']['includeSignatures'] ?? false,
                dateFormat: $validated['options']['dateFormat'] ?? 'Y-m-d',
            );

            $html = $this->templateService->previewTemplate(
                $validated['content'],
                $validated['previewData'] ?? [],
                $options
            );

            return response()->json(['html' => $html]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}

// App/Domain/Template/Controllers/InvoiceGenerationController.php
namespace App\Domain\Template\Controllers;

use App\Domain\Invoice\Models\Invoice;
use App\Domain\Template\DTOs\TemplateOptionsDTO;
use App\Domain\Template\Requests\GenerateInvoiceRequest;
use App\Domain\Template\Services\InvoiceGeneratorService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class InvoiceGenerationController extends Controller
{
    public function __construct(
        private readonly InvoiceGeneratorService $generatorService,
    ) {}

    public function generate(GenerateInvoiceRequest $request, Invoice $invoice): Response
    {

        try {
            $validated = $request->validated();
            
            $options = new TemplateOptionsDTO(
                language: $validated['language'] ?? config('app.locale', 'en'),
                timezone: $validated['timezone'] ?? config('app.timezone', 'UTC'),
                accentColor: $validated['accentColor'] ?? '#3B82F6',
                secondaryColor: $validated['secondaryColor'] ?? '#6B7280',
                includeLogo: $validated['includeLogo'] ?? true,
                includeSignatures: $validated['includeSignatures'] ?? false,
                dateFormat: $validated['dateFormat'] ?? 'Y-m-d',
            );

            $pdfContent = $this->generatorService->generatePdf(
                $invoice,
                $validated['template'] ?? 'default',
                $options
            );

            $filename = "invoice-{$invoice->id}.pdf";

            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', "inline; filename=\"{$filename}\"")
                ->header('Content-Length', (string) strlen($pdfContent));
                
        } catch (\Exception $e) {
            abort(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage());
        }
    }
}

// App/Domain/Template/Requests/CreateInvoiceTemplateRequest.php
namespace App\Domain\Template\Requests;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class CreateInvoiceTemplateRequest extends BaseFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('invoice_templates')
                    ->where('tenant_id', auth()->user()->tenant_id)
                    ->where('user_id', auth()->id())
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'content' => ['required', 'string'],
            'category' => ['nullable', 'string', 'in:invoice,quote,receipt,estimate'],
            'previewData' => ['nullable', 'array'],
        ];
    }
}

// App/Domain/Template/Requests/UpdateInvoiceTemplateRequest.php
namespace App\Domain\Template\Requests;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class UpdateInvoiceTemplateRequest extends BaseFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('invoice_templates')
                    ->where('tenant_id', auth()->user()->tenant_id)
                    ->where('user_id', auth()->id())
                    ->ignore($this->route('template'))
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'content' => ['sometimes', 'required', 'string'],
            'category' => ['nullable', 'string', 'in:invoice,quote,receipt,estimate'],
            'previewData' => ['nullable', 'array'],
        ];
    }
}

// App/Domain/Template/Requests/PreviewTemplateRequest.php
namespace App\Domain\Template\Requests;

use App\Http\Requests\BaseFormRequest;

class PreviewTemplateRequest extends BaseFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'content' => ['required', 'string'],
            'previewData' => ['nullable', 'array'],
            'options' => ['nullable', 'array'],
            'options.language' => ['nullable', 'string', 'in:en,pl,uk,ru'],
            'options.timezone' => ['nullable', 'string'],
            'options.accentColor' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'options.secondaryColor' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'options.includeLogo' => ['nullable', 'boolean'],
            'options.includeSignatures' => ['nullable', 'boolean'],
            'options.dateFormat' => ['nullable', 'string'],
        ];
    }
}

// App/Domain/Template/Requests/GenerateInvoiceRequest.php
namespace App\Domain\Template\Requests;

use App\Http\Requests\BaseFormRequest;

class GenerateInvoiceRequest extends BaseFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'template' => ['nullable', 'string'],
            'language' => ['nullable', 'string', 'in:en,pl,uk,ru'],
            'timezone' => ['nullable', 'string'],
            'accentColor' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondaryColor' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'includeLogo' => ['nullable', 'boolean'],
            'includeSignatures' => ['nullable', 'boolean'],
            'dateFormat' => ['nullable', 'string'],
            'action' => ['nullable', 'string', 'in:view,download'],
        ];
    }
}

// App/Domain/Template/Resources/InvoiceTemplateResource.php
namespace App\Domain\Template\Resources;

use App\Domain\Template\Models\InvoiceTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read InvoiceTemplate $resource
 */
class InvoiceTemplateResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'description' => $this->resource->description,
            'content' => $this->resource->content,
            'category' => $this->resource->category->value,
            'isActive' => $this->resource->is_active,
            'isDefault' => $this->resource->is_default,
            'isSystem' => $this->resource->user_id === null,
            'createdAt' => $this->resource->created_at?->toISOString(),
            'updatedAt' => $this->resource->updated_at?->toISOString(),
        ];
    }
}

// App/Domain/Template/Models/InvoiceTemplate.php
namespace App\Domain\Template\Models;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\BaseModel;
use App\Domain\Template\Casts\TemplatePreviewDataCast;
use App\Domain\Template\Casts\TemplateSettingsCast;
use App\Domain\Template\Enums\TemplateCategory;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $name
 * @property ?string $description
 * @property string $content
 * @property array $preview_data
 * @property bool $is_active
 * @property bool $is_default
 * @property ?string $user_id
 * @property TemplateCategory $category
 * @property array $settings
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ?Carbon $deleted_at
 * @property ?User $user
 */
class InvoiceTemplate extends BaseModel
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'content',
        'preview_data',
        'is_active',
        'is_default',
        'user_id',
        'category',
        'settings',
    ];

    protected $casts = [
        'preview_data' => TemplatePreviewDataCast::class,
        'settings' => TemplateSettingsCast::class,
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'category' => TemplateCategory::class,
        'deleted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

// Database Migration - create_invoice_templates_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceTemplatesTable extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_templates', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->longText('content');
            $table->json('preview_data')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->ulid('user_id')->nullable();
            $table->string('category')->default('invoice');
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Multi-tenant indexes
            $table->index(['tenant_id', 'user_id', 'is_active']);
            $table->index(['tenant_id', 'category', 'is_active']);
            $table->index(['tenant_id', 'name', 'is_active']);
            
            // Ensure unique template names per tenant/user combination
            $table->unique(['tenant_id', 'name', 'user_id']);
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('invoice_templates');
    }
}

// Database Factory - Database/Factories/InvoiceTemplateFactory.php
namespace Database\Factories;

use App\Domain\Template\Enums\TemplateCategory;
use App\Domain\Template\Models\InvoiceTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvoiceTemplate>
 */
class InvoiceTemplateFactory extends Factory
{
    protected $model = InvoiceTemplate::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => $this->faker->uuid(),
            'name' => $this->faker->unique()->words(3, true),
            'description' => $this->faker->sentence(),
            'content' => $this->faker->text(1000),
            'preview_data' => [],
            'is_active' => true,
            'is_default' => false,
            'user_id' => null,
            'category' => $this->faker->randomElement(TemplateCategory::cases()),
            'settings' => [],
        ];
    }

    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
        ]);
    }

    public function userTemplate(string $userId): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $userId,
        ]);
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }
}

// Routes - add to routes/api/templates.php (create new file following existing pattern)
<?php

use App\Domain\Template\Controllers\InvoiceTemplateController;
use App\Domain\Template\Controllers\InvoiceGenerationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'is_active', 'is_in_tenant'])->group(function () {
    // Invoice Templates
    Route::apiResource('invoice-templates', InvoiceTemplateController::class);
    Route::post('invoice-templates/preview', [InvoiceTemplateController::class, 'preview'])->name('invoice-templates.preview');
    
    // Invoice PDF Generation 
    Route::post('invoices/{invoice}/generate-pdf', [InvoiceGenerationController::class, 'generate'])->name('invoices.generate-pdf');
});

// Then add to main routes/api.php:
// require __DIR__ . '/api/templates.php';
```
