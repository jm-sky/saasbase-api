```php
<?php

// App/Domain/Template/Services/InvoiceGeneratorService.php
namespace App\Domain\Template\Services;

use App\Domain\Financial\DTOs\InvoiceDTO;
use App\Domain\Template\DTOs\TemplateOptionsDTO;
use App\Domain\Template\Exceptions\TemplateNotFoundException;
use App\Domain\Template\Exceptions\TemplateRenderingException;
use Mpdf\Mpdf;

class InvoiceGeneratorService
{
    public function __construct(
        private readonly TemplatingService $templatingService,
        private readonly InvoiceTemplateService $templateService,
        private readonly InvoiceToTemplateTransformer $transformer,
    ) {}
    
    public function generatePdf(
        InvoiceDTO $invoice, 
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
            $html = $this->templatingService->render($template->content, $templateInvoice, $options);
            
            // Generate PDF with page numbers
            return $this->generatePdfFromHtml($html, $options);
        } catch (\Exception $e) {
            throw new TemplateRenderingException($e->getMessage(), $e);
        }
    }
    
    private function generatePdfFromHtml(string $html, TemplateOptionsDTO $options): string
    {
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_top' => 20,
            'margin_bottom' => 30, // Extra space for footer
            'margin_left' => 15,
            'margin_right' => 15,
            'default_font' => 'arial',
        ]);
        
        // Set footer with page numbers
        $mpdf->SetHTMLFooter('
            <div style="text-align: center; font-size: 10px; color: #666;">
                ' . __('invoices.page') . ' {PAGENO}/{nbpg}
            </div>
        ');
        
        // Add CSS with custom colors
        $css = $this->generateCSS($options);
        $mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
        
        // Add HTML content
        $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
        
        return $mpdf->Output('', 'S');
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

// App/Http/Controllers/Api/Template/InvoiceTemplateController.php
namespace App\Http\Controllers\Api\Template;

use App\Domain\Template\Services\InvoiceTemplateService;
use App\Domain\Template\Services\TemplatingService;
use App\Domain\Template\DTOs\TemplateOptionsDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Template\StoreTemplateRequest;
use App\Http\Requests\Template\UpdateTemplateRequest;
use App\Http\Requests\Template\PreviewTemplateRequest;
use App\Http\Resources\Template\InvoiceTemplateResource;
use App\Domain\Template\Models\InvoiceTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class InvoiceTemplateController extends Controller
{
    public function __construct(
        private readonly InvoiceTemplateService $templateService,
        private readonly TemplatingService $templatingService,
    ) {}

    public function index(): JsonResponse
    {
        /** @var string|null $userId */
        $userId = Auth::id();
        
        $templates = $this->templateService->getAvailableTemplates($userId);
        
        return response()->json([
            'system' => InvoiceTemplateResource::collection($templates['system']),
            'user' => InvoiceTemplateResource::collection($templates['user']),
        ]);
    }

    public function store(StoreTemplateRequest $request): JsonResponse
    {
        try {
            /** @var string|null $userId */
            $userId = Auth::id();
            
            $template = $this->templateService->createTemplate(
                $request->validated(),
                $userId
            );

            return response()->json(
                new InvoiceTemplateResource($template),
                Response::HTTP_CREATED
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function show(string $templateName): JsonResponse
    {
        /** @var string|null $userId */
        $userId = Auth::id();
        
        $template = $this->templateService->getTemplate($templateName, $userId);

        if (!$template) {
            return response()->json([
                'error' => __('invoices.template_not_found')
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json(new InvoiceTemplateResource($template));
    }

    public function update(UpdateTemplateRequest $request, InvoiceTemplate $template): JsonResponse
    {
        try {
            /** @var string|null $userId */
            $userId = Auth::id();
            
            $updatedTemplate = $this->templateService->updateTemplate(
                $template->id,
                $request->validated(),
                $userId
            );

            return response()->json(new InvoiceTemplateResource($updatedTemplate));
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\UnauthorizedException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], Response::HTTP_FORBIDDEN);
        }
    }

    public function destroy(InvoiceTemplate $template): JsonResponse
    {
        /** @var string|null $userId */
        $userId = Auth::id();
        
        if ($template->user_id !== $userId && $template->user_id !== null) {
            return response()->json([
                'error' => __('invoices.unauthorized_template_access')
            ], Response::HTTP_FORBIDDEN);
        }

        $template->delete();

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

// App/Http/Controllers/Api/Invoice/InvoiceGenerationController.php
namespace App\Http\Controllers\Api\Invoice;

use App\Domain\Financial\DTOs\InvoiceDTO;
use App\Domain\Financial\Models\Invoice;
use App\Domain\Template\DTOs\TemplateOptionsDTO;
use App\Domain\Template\Services\InvoiceGeneratorService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Invoice\GenerateInvoiceRequest;
use Illuminate\Http\Response;

class InvoiceGenerationController extends Controller
{
    public function __construct(
        private readonly InvoiceGeneratorService $generatorService,
    ) {}

    public function generate(GenerateInvoiceRequest $request, string $invoiceId): Response
    {
        /** @var Invoice|null $invoice */
        $invoice = Invoice::with(['items', 'customer'])->find($invoiceId);
        
        if (!$invoice) {
            abort(Response::HTTP_NOT_FOUND, __('invoices.invoice_not_found'));
        }

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

            // Convert Eloquent model to DTO (you would implement this based on your existing DTO mapping)
            $invoiceDTO = $this->mapToInvoiceDTO($invoice);
            
            $pdfContent = $this->generatorService->generatePdf(
                $invoiceDTO,
                $validated['template'] ?? 'default',
                $options
            );

            $filename = "invoice-{$invoice->number}.pdf";

            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', "inline; filename=\"{$filename}\"")
                ->header('Content-Length', (string) strlen($pdfContent));
                
        } catch (\Exception $e) {
            abort(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage());
        }
    }

    /**
     * Map Eloquent model to DTO - implement this based on your existing DTO mapping logic
     */
    private function mapToInvoiceDTO(Invoice $invoice): InvoiceDTO
    {
        // This would use your existing DTO mapping logic
        // Placeholder - replace with your actual mapping service/transformer
        throw new \Exception('Implement DTO mapping based on your existing structure');
    }
}

// App/Http/Requests/Template/StoreTemplateRequest.php
namespace App\Http\Requests\Template;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTemplateRequest extends FormRequest
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

// App/Http/Requests/Template/UpdateTemplateRequest.php
namespace App\Http\Requests\Template;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTemplateRequest extends FormRequest
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

// App/Http/Requests/Template/PreviewTemplateRequest.php
namespace App\Http\Requests\Template;

use Illuminate\Foundation\Http\FormRequest;

class PreviewTemplateRequest extends FormRequest
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

// App/Http/Requests/Invoice/GenerateInvoiceRequest.php
namespace App\Http\Requests\Invoice;

use Illuminate\Foundation\Http\FormRequest;

class GenerateInvoiceRequest extends FormRequest
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

// App/Http/Resources/Template/InvoiceTemplateResource.php
namespace App\Http\Resources\Template;

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

// Routes - add to routes/api.php
/*
Route::prefix('templates')->name('templates.')->group(function () {
    Route::get('/invoice', [InvoiceTemplateController::class, 'index'])->name('index');
    Route::post('/invoice', [InvoiceTemplateController::class, 'store'])->name('store');
    Route::get('/invoice/{templateName}', [InvoiceTemplateController::class, 'show'])->name('show');
    Route::put('/invoice/{template}', [InvoiceTemplateController::class, 'update'])->name('update');
    Route::delete('/invoice/{template}', [InvoiceTemplateController::class, 'destroy'])->name('destroy');
    Route::post('/invoice/preview', [InvoiceTemplateController::class, 'preview'])->name('preview');
});

Route::prefix('invoices')->name('invoices.')->group(function () {
    Route::post('/{invoice}/generate', [InvoiceGenerationController::class, 'generate'])->name('generate');
});
*/
```
