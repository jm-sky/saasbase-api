<?php

namespace App\Domain\Expense\Controllers;

use App\Domain\Common\Filters\AdvancedFilter;
use App\Domain\Common\Filters\ComboSearchFilter;
use App\Domain\Common\Filters\DateRangeFilter;
use App\Domain\Common\Jobs\StartOcrJob;
use App\Domain\Common\Sorts\JsonbPathSort;
use App\Domain\Common\Traits\HasActivityLogging;
use App\Domain\Common\Traits\HasIndexQuery;
use App\Domain\Expense\Actions\CreateExpenseForOcr;
use App\Domain\Expense\Models\Expense;
use App\Domain\Expense\Requests\StoreExpenseRequest;
use App\Domain\Expense\Requests\UpdateExpenseRequest;
use App\Domain\Expense\Requests\UploadExpenseOcrRequest;
use App\Domain\Expense\Resources\ExpenseResource;
use App\Domain\Export\DTOs\ExportConfigDTO;
use App\Domain\Export\Exports\ExpensesExport;
use App\Domain\Export\Services\ExportService;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

class ExpenseController extends Controller
{
    use HasIndexQuery;
    use HasActivityLogging;
    use AuthorizesRequests;

    protected int $defaultPerPage = 15;

    private ExportService $exportService;

    public function __construct()
    {
        $this->modelClass  = Expense::class;
        $this->defaultWith = [];

        $this->filters = [
            AllowedFilter::custom('search', new ComboSearchFilter(['number', 'type', 'status'])),
            AllowedFilter::custom('type', new AdvancedFilter()),
            AllowedFilter::custom('status', new AdvancedFilter()),
            AllowedFilter::custom('number', new AdvancedFilter()),
            AllowedFilter::custom('currency', new AdvancedFilter()),
            AllowedFilter::custom('issueDate', new DateRangeFilter('issue_date')),
            AllowedFilter::custom('createdAt', new DateRangeFilter('created_at')),
            AllowedFilter::custom('updatedAt', new DateRangeFilter('updated_at')),
        ];

        $this->sorts = [
            AllowedSort::custom('seller', new JsonbPathSort('seller', 'name')),
            AllowedSort::field('number'),
            AllowedSort::field('type'),
            AllowedSort::field('status'),
            AllowedSort::field('issueDate', 'issue_date'),
            AllowedSort::field('totalNet', 'total_net'),
            AllowedSort::field('totalTax', 'total_tax'),
            AllowedSort::field('totalGross', 'total_gross'),
            AllowedSort::field('currency'),
            AllowedSort::field('createdAt', 'created_at'),
            AllowedSort::field('updatedAt', 'updated_at'),
        ];

        $this->defaultSort   = '-created_at';
        $this->exportService = app(ExportService::class);
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $expenses = $this->getIndexPaginator($request);

        return ExpenseResource::collection($expenses['data'])
            ->additional(['meta' => $expenses['meta']])
        ;
    }

    public function store(StoreExpenseRequest $request): JsonResponse
    {
        $expense = Expense::create($request->validated());

        return response()->json(new ExpenseResource($expense), Response::HTTP_CREATED);
    }

    public function show(Expense $expense): ExpenseResource
    {
        return new ExpenseResource($expense);
    }

    public function update(UpdateExpenseRequest $request, Expense $expense): JsonResponse
    {
        $expense->update($request->validated());

        return response()->json(new ExpenseResource($expense));
    }

    public function destroy(Expense $expense): JsonResponse
    {
        $expense->delete();

        return response()->json(['message' => 'Expense deleted successfully.'], Response::HTTP_NO_CONTENT);
    }

    public function search(Request $request): JsonResponse|AnonymousResourceCollection
    {
        $query   = $request->input('q');
        $perPage = $request->input('perPage', $this->defaultPerPage);

        if (!$query) {
            return response()->json(['message' => 'Search query is required'], Response::HTTP_BAD_REQUEST);
        }

        $results = Expense::search($query)
            ->query(function ($builder) use ($request) {
                return $this->getIndexQuery($request);
            })
            ->paginate($perPage)
        ;

        return ExpenseResource::collection($results);
    }

    /**
     * Export products as Excel file.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request)
    {
        $config = new ExportConfigDTO(
            filters: $request->all(),
            columns: $request->get('columns', []),
            formatting: $request->get('formatting', [])
        );

        return $this->exportService->download(
            ExpensesExport::class,
            $config,
            'expenses.xlsx'
        );
    }

    /**
     * Upload files for OCR and create empty expenses for each file.
     */
    public function uploadForOcr(UploadExpenseOcrRequest $request): AnonymousResourceCollection|JsonResponse
    {
        $createdExpenses = [];

        foreach ($request->file('files') as $file) {
            $expense = CreateExpenseForOcr::handle($file);

            StartOcrJob::dispatch($expense->ocrRequest);

            $createdExpenses[] = $expense;
        }

        return ExpenseResource::collection(collect($createdExpenses));
    }

    public function startOcr(Expense $expense): JsonResponse
    {
        if (!$expense->ocrRequest) {
            return response()->json(['message' => 'OCR is not pending.'], Response::HTTP_BAD_REQUEST);
        }

        StartOcrJob::dispatch($expense->ocrRequest);

        return response()->json(['message' => 'OCR started successfully.']);
    }
}
