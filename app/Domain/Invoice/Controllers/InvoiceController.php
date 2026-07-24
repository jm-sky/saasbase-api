<?php

namespace App\Domain\Invoice\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Filters\AdvancedFilter;
use App\Domain\Common\Filters\ComboSearchFilter;
use App\Domain\Common\Filters\DateRangeFilter;
use App\Domain\Common\Sorts\JsonbPathSort;
use App\Domain\Common\Support\RelativeRouteUrl;
use App\Domain\Common\Traits\HasActivityLogging;
use App\Domain\Common\Traits\HasIndexQuery;
use App\Domain\Export\DTOs\ExportConfigDTO;
use App\Domain\Export\Exports\InvoicesExport;
use App\Domain\Export\Services\ExportService;
use App\Domain\Invoice\Models\Invoice;
use App\Domain\Invoice\Requests\InvoicePdfRequest;
use App\Domain\Invoice\Requests\StoreInvoiceRequest;
use App\Domain\Invoice\Requests\UpdateInvoiceRequest;
use App\Domain\Invoice\Resources\InvoiceResource;
use App\Domain\Rights\Enums\RoleName;
use App\Domain\Rights\Support\TenantScopedRoles;
use App\Domain\Template\Services\InvoiceGeneratorService;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class InvoiceController extends Controller
{
    use AuthorizesRequests;
    use HasActivityLogging;
    use HasIndexQuery;

    protected int $defaultPerPage = 15;

    private ExportService $exportService;

    private InvoiceGeneratorService $invoiceGenerator;

    public function __construct()
    {
        $this->modelClass = Invoice::class;
        $this->defaultWith = ['numberingTemplate'];

        $this->filters = [
            AllowedFilter::custom('search', new ComboSearchFilter(['number', 'type', 'status'])),
            AllowedFilter::custom('type', new AdvancedFilter),
            AllowedFilter::custom('status', new AdvancedFilter),
            AllowedFilter::custom('number', new AdvancedFilter),
            AllowedFilter::custom('numberingTemplateId', new AdvancedFilter, 'numbering_template_id'),
            AllowedFilter::custom('currency', new AdvancedFilter),
            AllowedFilter::custom('issueDate', new DateRangeFilter('issue_date')),
            AllowedFilter::custom('createdAt', new DateRangeFilter('created_at')),
            AllowedFilter::custom('updatedAt', new DateRangeFilter('updated_at')),
        ];

        $this->sorts = [
            AllowedSort::custom('buyer', new JsonbPathSort('buyer', 'name')),
            'number',
            'type',
            'status',
            'issueDate' => 'issue_date',
            'totalNet' => 'total_net',
            'totalTax' => 'total_tax',
            'totalGross' => 'total_gross',
            'currency',
            'createdAt' => 'created_at',
            'updatedAt' => 'updated_at',
        ];

        $this->defaultSort = '-issue_date';
        $this->exportService = app(ExportService::class);
        $this->invoiceGenerator = app(InvoiceGeneratorService::class);
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $invoices = $this->getIndexPaginator($request);

        return InvoiceResource::collection($invoices['data'])
            ->additional(['meta' => $invoices['meta']]);
    }

    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $this->authorize('create', Invoice::class);

        $invoice = Invoice::create($request->validated());

        return response()->json([
            'data' => new InvoiceResource($invoice),
        ], Response::HTTP_CREATED);
    }

    public function show(Invoice $invoice): InvoiceResource
    {
        $invoice->load('numberingTemplate');

        return new InvoiceResource($invoice);
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): JsonResponse
    {
        $this->authorize('update', $invoice);

        $invoice->update($request->validated());

        return response()->json(new InvoiceResource($invoice));
    }

    public function destroy(Invoice $invoice): JsonResponse
    {
        $this->authorize('delete', $invoice);

        $invoice->delete();

        return response()->json(['message' => 'Invoice deleted successfully.'], Response::HTTP_NO_CONTENT);
    }

    public function search(Request $request): JsonResponse|AnonymousResourceCollection
    {
        $query = $request->input('q');
        $perPage = $request->input('perPage', $this->defaultPerPage);

        if (! $query) {
            return response()->json(['message' => 'Search query is required'], Response::HTTP_BAD_REQUEST);
        }

        $results = Invoice::search($query)
            ->query(function ($builder) use ($request) {
                return $this->getIndexQuery($request);
            })
            ->paginate($perPage);

        return InvoiceResource::collection($results);
    }

    /**
     * Export products as Excel file.
     *
     * @return BinaryFileResponse
     */
    public function export(Request $request)
    {
        $this->authorizeManage();

        $config = new ExportConfigDTO(
            filters: $request->all(),
            columns: $request->get('columns', []),
            formatting: $request->get('formatting', [])
        );

        return $this->exportService->download(
            InvoicesExport::class,
            $config,
            'invoices.xlsx'
        );
    }

    /**
     * Bulk-exporting every invoice (amounts, buyer/seller data) is
     * destructive/broad enough to require Owner/Admin, unlike everyday
     * CRUD which any tenant member needs — same pattern as
     * ContractorController/ProductController::export().
     */
    private function authorizeManage(): void
    {
        /** @var User $user */
        $user = Auth::user();
        $tenantId = $user->getTenantId();

        abort_unless(
            $tenantId && TenantScopedRoles::userHasAnyRole($user, $tenantId, [RoleName::Owner->value, RoleName::Admin->value]),
            Response::HTTP_FORBIDDEN
        );
    }

    /**
     * Generate PDF for an invoice.
     * Supports multiple actions: download, stream, attach, preview.
     */
    public function generatePdf(InvoicePdfRequest $request, Invoice $invoice): mixed
    {
        $action = $request->getAction();

        return match ($action) {
            'attach' => $this->attachPdf($request, $invoice),
            'download' => $this->downloadPdf($request, $invoice),
            'stream' => $this->streamPdf($request, $invoice),
            'preview' => $this->previewPdf($request, $invoice),
            default => $this->downloadPdf($request, $invoice),
        };
    }

    /**
     * Download invoice PDF.
     */
    public function downloadPdf(InvoicePdfRequest $request, Invoice $invoice): Response
    {
        $templateId = $request->getTemplateId();

        return $this->invoiceGenerator->downloadPdf($invoice, $templateId);
    }

    /**
     * Stream invoice PDF.
     */
    public function streamPdf(InvoicePdfRequest $request, Invoice $invoice): \Symfony\Component\HttpFoundation\Response
    {
        $templateId = $request->getTemplateId();

        return $this->invoiceGenerator->streamPdf($invoice, $templateId);
    }

    /**
     * Generate and attach PDF to invoice.
     */
    public function attachPdf(InvoicePdfRequest $request, Invoice $invoice): JsonResponse
    {
        $templateId = $request->getTemplateId();
        $collection = $request->getCollection();
        $language = $request->getLanguage();

        $media = $this->invoiceGenerator->generateAndAttachPdf(
            $invoice,
            $templateId,
            $collection,
            $language,
        );

        return response()->json([
            'message' => 'PDF generated and attached successfully.',
            'data' => [
                'mediaId' => $media->id,
                'fileName' => $media->file_name,
                'size' => $media->size,
                'collectionName' => $media->collection_name,
                'url' => RelativeRouteUrl::generate('invoices.attachments.download', [$invoice, $media]),
                'templateName' => $this->invoiceGenerator->lastUsedInvoiceTemplate?->name,
            ],
        ], Response::HTTP_CREATED);
    }

    /**
     * Preview invoice as HTML (without generating PDF).
     */
    public function previewPdf(InvoicePdfRequest $request, Invoice $invoice): JsonResponse
    {
        $templateId = $request->getTemplateId();

        $htmlContent = $this->invoiceGenerator->previewHtml($invoice, $templateId);

        return response()->json([
            'data' => [
                'html_content' => $htmlContent,
            ],
        ]);
    }
}
