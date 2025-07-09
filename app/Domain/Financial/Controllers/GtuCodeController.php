<?php

namespace App\Domain\Financial\Controllers;

use App\Domain\Common\Filters\AdvancedFilter;
use App\Domain\Common\Filters\ComboSearchFilter;
use App\Domain\Common\Traits\HasIndexQuery;
use App\Domain\Financial\Models\GtuCode;
use App\Domain\Financial\Requests\AssignGtuToInvoiceLineRequest;
use App\Domain\Financial\Requests\AssignGtuToProductRequest;
use App\Domain\Financial\Requests\GtuForInvoiceRequest;
use App\Domain\Financial\Requests\SearchGtuCodeRequest;
use App\Domain\Financial\Requests\StoreGtuCodeRequest;
use App\Domain\Financial\Resources\GtuCodeResource;
use App\Domain\Financial\Services\GTUAssignmentService;
use App\Domain\Invoice\Models\Invoice;
use App\Domain\Products\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Spatie\QueryBuilder\AllowedFilter;

class GtuCodeController extends Controller
{
    use HasIndexQuery;

    protected int $defaultPerPage = 15;

    public function __construct()
    {
        $this->modelClass  = GtuCode::class;
        $this->defaultWith = [];

        $this->filters = [
            AllowedFilter::custom('search', new ComboSearchFilter(['name'])), // Update with actual searchable fields
            AllowedFilter::custom('id', new AdvancedFilter()),
            AllowedFilter::custom('name', new AdvancedFilter()), // Update with actual fields
            AllowedFilter::custom('createdAt', new AdvancedFilter(), 'created_at'),
            AllowedFilter::custom('updatedAt', new AdvancedFilter(), 'updated_at'),
        ];

        $this->sorts = [
            'name', // Update with actual sortable fields
            'createdAt' => 'created_at',
            'updatedAt' => 'updated_at',
        ];

        $this->defaultSort = '-created_at';
    }

    public function index(SearchGtuCodeRequest $request): JsonResponse
    {
        $result = $this->getIndexPaginator($request);

        return response()->json([
            'data' => GtuCodeResource::collection($result['data']),
            'meta' => $result['meta'],
        ]);
    }

    public function store(StoreGtuCodeRequest $request): JsonResponse
    {
        $gtuCode = GtuCode::create($request->validated());

        return response()->json([
            'data' => new GtuCodeResource($gtuCode),
        ], Response::HTTP_CREATED);
    }

    public function show(GtuCode $gtuCode): JsonResponse
    {
        return response()->json([
            'data' => new GtuCodeResource($gtuCode),
        ]);
    }

    public function assignToInvoiceLine(AssignGtuToInvoiceLineRequest $request, string $invoiceId, string $lineId): JsonResponse
    {
        $invoice              = Invoice::findOrFail($invoiceId);
        $gtuAssignmentService = app(GTUAssignmentService::class);

        $body         = $invoice->body;
        $updatedLines = [];

        foreach ($body->lines as $line) {
            if ($line->id === $lineId) {
                $line = $gtuAssignmentService->assignGTUCode($line, $request->input('gtu_code'));
            }
            $updatedLines[] = $line;
        }

        // Update invoice body
        $updatedBody = new \App\Domain\Financial\DTOs\InvoiceBodyDTO(
            lines: $updatedLines,
            vatSummary: $body->vatSummary,
            exchange: $body->exchange,
            description: $body->description,
        );

        $invoice->body = $updatedBody;
        $invoice->save();

        return response()->json([
            'message' => 'GTU code assigned successfully.',
            'data'    => ['invoice_id' => $invoiceId, 'line_id' => $lineId, 'gtu_code' => $request->input('gtu_code')],
        ]);
    }

    public function removeFromInvoiceLine(string $invoiceId, string $lineId, string $gtuCode): JsonResponse
    {
        $invoice              = Invoice::findOrFail($invoiceId);
        $gtuAssignmentService = app(GTUAssignmentService::class);

        $body         = $invoice->body;
        $updatedLines = [];

        foreach ($body->lines as $line) {
            if ($line->id === $lineId) {
                $line = $gtuAssignmentService->removeGTUCode($line, $gtuCode);
            }
            $updatedLines[] = $line;
        }

        // Update invoice body
        $updatedBody = new \App\Domain\Financial\DTOs\InvoiceBodyDTO(
            lines: $updatedLines,
            vatSummary: $body->vatSummary,
            exchange: $body->exchange,
            description: $body->description,
        );

        $invoice->body = $updatedBody;
        $invoice->save();

        return response()->json([
            'message' => 'GTU code removed successfully.',
        ]);
    }

    public function suggest(string $productId): JsonResponse
    {
        $product              = Product::findOrFail($productId);
        $gtuAssignmentService = app(GTUAssignmentService::class);

        $suggestions = [];

        // Get codes from product
        $suggestions = array_merge($suggestions, $product->getGtuCodes());

        // Detect by category
        $suggestions = array_merge($suggestions, $gtuAssignmentService->detectGTUByProductCategory($product));

        // Detect by keywords in name/description
        $description = $product->name . ' ' . ($product->description ?? '');
        $suggestions = array_merge($suggestions, $gtuAssignmentService->detectGTUByKeywords($description));

        $suggestions = array_unique($suggestions);

        return response()->json([
            'data' => [
                'product_id'          => $productId,
                'suggested_gtu_codes' => $suggestions,
            ],
        ]);
    }

    public function assignToProduct(AssignGtuToProductRequest $request, string $productId): JsonResponse
    {
        $product              = Product::findOrFail($productId);
        $gtuAssignmentService = app(GTUAssignmentService::class);

        $updatedProduct = $gtuAssignmentService->assignGTUToProduct($product, $request->input('gtu_code'), $request->user());

        return response()->json([
            'message' => 'GTU code assigned to product successfully.',
            'data'    => [
                'product_id' => $productId,
                'gtu_codes'  => $updatedProduct->getGtuCodes(),
            ],
        ]);
    }

    public function autoAssign(GtuForInvoiceRequest $request): JsonResponse
    {
        $invoice              = Invoice::findOrFail($request->input('invoice_id'));
        $gtuAssignmentService = app(GTUAssignmentService::class);

        $updatedInvoice = $gtuAssignmentService->processInvoiceGTUAssignments($invoice);

        return response()->json([
            'message' => 'GTU codes auto-assigned successfully.',
            'data'    => [
                'invoice_id'      => $updatedInvoice->id,
                'lines_processed' => count($updatedInvoice->body->lines),
            ],
        ]);
    }

    public function validateAssignment(GtuForInvoiceRequest $request): JsonResponse
    {
        $invoice              = Invoice::findOrFail($request->input('invoice_id'));
        $gtuAssignmentService = app(GTUAssignmentService::class);

        $isValid = $gtuAssignmentService->validateInvoiceGTUCompliance($invoice);

        return response()->json([
            'data' => [
                'invoice_id'   => $invoice->id,
                'is_compliant' => $isValid,
            ],
        ]);
    }
}
