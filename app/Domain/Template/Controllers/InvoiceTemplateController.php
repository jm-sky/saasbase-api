<?php

namespace App\Domain\Template\Controllers;

use App\Domain\Common\Filters\AdvancedFilter;
use App\Domain\Common\Filters\ComboSearchFilter;
use App\Domain\Common\Traits\HasIndexQuery;
use App\Domain\Template\Models\InvoiceTemplate;
use App\Domain\Template\Requests\CreateInvoiceTemplateRequest;
use App\Domain\Template\Requests\PreviewInvoiceTemplateRequest;
use App\Domain\Template\Requests\UpdateInvoiceTemplateRequest;
use App\Domain\Template\Resources\InvoiceTemplatePreviewResource;
use App\Domain\Template\Resources\InvoiceTemplateResource;
use App\Domain\Template\Services\InvoiceGeneratorService;
use App\Domain\Template\Services\TemplatingService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\QueryBuilder\AllowedFilter;

class InvoiceTemplateController extends Controller
{
    use HasIndexQuery;

    protected int $defaultPerPage = 15;

    public function __construct(private readonly TemplatingService $templatingService)
    {
        $this->modelClass  = InvoiceTemplate::class;
        $this->defaultWith = ['user'];

        $this->filters = [
            AllowedFilter::custom('search', new ComboSearchFilter(['name', 'description'])),
            AllowedFilter::custom('name', new AdvancedFilter()),
            AllowedFilter::custom('description', new AdvancedFilter()),
            AllowedFilter::custom('category', new AdvancedFilter()),
            AllowedFilter::custom('userId', new AdvancedFilter(), 'user_id'),
            AllowedFilter::custom('isActive', new AdvancedFilter(), 'is_active'),
            AllowedFilter::custom('isDefault', new AdvancedFilter(), 'is_default'),
            AllowedFilter::custom('createdAt', new AdvancedFilter(), 'created_at'),
            AllowedFilter::custom('updatedAt', new AdvancedFilter(), 'updated_at'),
        ];

        $this->sorts = [
            'name',
            'description',
            'category',
            'isActive'  => 'is_active',
            'isDefault' => 'is_default',
            'createdAt' => 'created_at',
            'updatedAt' => 'updated_at',
        ];

        $this->defaultSort = '-created_at';
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', InvoiceTemplate::class);

        $templates = $this->getIndexPaginator($request);

        return InvoiceTemplatePreviewResource::collection($templates['data'])
            ->additional(['meta' => $templates['meta']])
        ;
    }

    public function store(CreateInvoiceTemplateRequest $request): JsonResponse
    {
        $this->authorize('create', InvoiceTemplate::class);

        $data = $request->validated();
        $this->validateHandlebarsContent($data['content'] ?? '');

        $template = DB::transaction(function () use ($data) {
            // If this template is set as default, unset other defaults in the same category
            if ($data['isDefault'] ?? false) {
                InvoiceTemplate::query()
                    ->where('tenant_id', $data['tenantId'])
                    ->where('category', $data['category'])
                    ->update(['is_default' => false])
                ;
            }

            return InvoiceTemplate::create($data);
        });

        $template->load(['user']);

        return response()->json([
            'message' => 'Invoice template created successfully.',
            'data'    => new InvoiceTemplateResource($template),
        ], Response::HTTP_CREATED);
    }

    public function show(InvoiceTemplate $invoiceTemplate): JsonResponse
    {
        $this->authorize('view', $invoiceTemplate);

        $invoiceTemplate->load(['user']);

        return response()->json([
            'data' => new InvoiceTemplateResource($invoiceTemplate),
        ]);
    }

    public function update(UpdateInvoiceTemplateRequest $request, InvoiceTemplate $invoiceTemplate): JsonResponse
    {
        $this->authorize('update', $invoiceTemplate);

        $data = $request->validated();

        if (\array_key_exists('content', $data)) {
            $this->validateHandlebarsContent($data['content']);
        }

        DB::transaction(function () use ($data, $invoiceTemplate) {
            // If this template is set as default, unset other defaults in the same category
            if (($data['isDefault'] ?? false) && (!$invoiceTemplate->is_default || $invoiceTemplate->category !== $data['category'])) {
                InvoiceTemplate::query()
                    ->where('tenant_id', $invoiceTemplate->tenant_id)
                    ->where('category', $data['category'])
                    ->update(['is_default' => false])
                ;
            }

            $invoiceTemplate->update($data);
        });

        $invoiceTemplate->load(['user']);

        return response()->json([
            'message' => 'Invoice template updated successfully.',
            'data'    => new InvoiceTemplateResource($invoiceTemplate->fresh()),
        ]);
    }

    public function destroy(InvoiceTemplate $invoiceTemplate): JsonResponse
    {
        $this->authorize('delete', $invoiceTemplate);

        $invoiceTemplate->delete();

        return response()->json([
            'message' => 'Invoice template deleted successfully.',
        ], Response::HTTP_NO_CONTENT);
    }

    public function setDefault(InvoiceTemplate $invoiceTemplate): JsonResponse
    {
        $this->authorize('setDefault', $invoiceTemplate);

        DB::transaction(function () use ($invoiceTemplate) {
            // Unset other defaults in the same category
            InvoiceTemplate::query()
                ->where('tenant_id', $invoiceTemplate->tenant_id)
                ->where('category', $invoiceTemplate->category)
                ->update(['is_default' => false])
            ;

            // Set this template as default
            $invoiceTemplate->update(['is_default' => true]);
        });

        $invoiceTemplate->load(['user']);

        return response()->json([
            'message' => 'Invoice template set as default successfully.',
            'data'    => new InvoiceTemplateResource($invoiceTemplate->fresh()),
        ]);
    }

    public function preview(PreviewInvoiceTemplateRequest $request, InvoiceGeneratorService $invoiceGeneratorService): JsonResponse
    {
        $this->authorize('preview', InvoiceTemplate::class);

        $templateContent = $request->getTemplateContent();
        $previewData     = $request->getPreviewData();
        $language        = $request->getLanguage();
        $options         = $request->getOptions();

        // Generate styled HTML using the service
        $styledHtml = $invoiceGeneratorService->generatePreviewHtml(
            $templateContent,
            $previewData,
            $language,
            $options
        );

        return response()->json([
            'html' => $styledHtml,
        ]);
    }

    /**
     * InvoiceTemplateService::create/update() already run this check, but
     * store()/update() bypass the service and write to the model directly —
     * without this, a broken Handlebars template saves fine and only blows
     * up later at PDF-generation time (TemplateRenderingException) instead
     * of at save time (422).
     */
    private function validateHandlebarsContent(string $content): void
    {
        if ($this->templatingService->validate($content)) {
            return;
        }

        throw ValidationException::withMessages(['content' => [$this->templatingService->getValidationErrors($content) ?? 'Invalid Handlebars template syntax.']]);
    }
}
