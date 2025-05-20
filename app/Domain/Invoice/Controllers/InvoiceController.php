<?php

namespace App\Domain\Invoice\Controllers;

use App\Domain\Common\Filters\AdvancedFilter;
use App\Domain\Common\Filters\ComboSearchFilter;
use App\Domain\Common\Filters\DateRangeFilter;
use App\Domain\Common\Traits\HasIndexQuery;
use App\Domain\Invoice\DTOs\InvoiceDTO;
use App\Domain\Invoice\Models\Invoice;
use App\Domain\Invoice\Requests\StoreInvoiceRequest;
use App\Domain\Invoice\Requests\UpdateInvoiceRequest;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\QueryBuilder\AllowedFilter;

class InvoiceController extends Controller
{
    use HasIndexQuery;
    use AuthorizesRequests;

    protected int $defaultPerPage = 15;

    public function __construct()
    {
        $this->modelClass  = Invoice::class;
        $this->defaultWith = ['numberingTemplate'];

        $this->filters = [
            AllowedFilter::custom('search', new ComboSearchFilter(['number', 'type', 'status'])),
            AllowedFilter::custom('type', new AdvancedFilter()),
            AllowedFilter::custom('status', new AdvancedFilter()),
            AllowedFilter::custom('number', new AdvancedFilter()),
            AllowedFilter::custom('numberingTemplateId', new AdvancedFilter(), 'numbering_template_id'),
            AllowedFilter::custom('currency', new AdvancedFilter()),
            AllowedFilter::custom('issueDate', new DateRangeFilter('issue_date')),
            AllowedFilter::custom('createdAt', new DateRangeFilter('created_at')),
            AllowedFilter::custom('updatedAt', new DateRangeFilter('updated_at')),
        ];

        $this->sorts = [
            'number',
            'type',
            'status',
            'issueDate'  => 'issue_date',
            'totalNet'   => 'total_net',
            'totalTax'   => 'total_tax',
            'totalGross' => 'total_gross',
            'currency',
            'createdAt' => 'created_at',
            'updatedAt' => 'updated_at',
        ];

        $this->defaultSort = '-created_at';
    }

    public function index(Request $request): JsonResponse
    {
        $result         = $this->getIndexPaginator($request);
        $result['data'] = InvoiceDTO::collect($result['data']);

        return response()->json($result);
    }

    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $invoice = Invoice::create($request->validated());

        return response()->json(
            ['data' => InvoiceDTO::from($invoice)],
            Response::HTTP_CREATED
        );
    }

    public function show(Invoice $invoice): JsonResponse
    {
        $invoice->load('numberingTemplate');

        return response()->json(['data' => InvoiceDTO::from($invoice)]);
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): JsonResponse
    {
        $invoice->update($request->validated());

        return response()->json(['data' => InvoiceDTO::from($invoice)]);
    }

    public function destroy(Invoice $invoice): Response
    {
        $invoice->delete();

        return response()->noContent();
    }
}
