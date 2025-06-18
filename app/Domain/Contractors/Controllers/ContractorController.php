<?php

namespace App\Domain\Contractors\Controllers;

use App\Domain\Common\Filters\AdvancedFilter;
use App\Domain\Common\Filters\ComboSearchFilter;
use App\Domain\Common\Traits\HasActivityLogging;
use App\Domain\Common\Traits\HasIndexQuery;
use App\Domain\Contractors\Enums\ContractorActivityType;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Contractors\Requests\SearchContractorRequest;
use App\Domain\Contractors\Requests\StoreContractorRequest;
use App\Domain\Contractors\Requests\UpdateContractorRequest;
use App\Domain\Contractors\Resources\ContractorResource;
use App\Domain\Export\DTOs\ExportConfigDTO;
use App\Domain\Export\Exports\ContractorsExport;
use App\Domain\Export\Services\ExportService;
use App\Http\Controllers\Controller;
use App\Services\LogoFetcher\LogoFetcherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Spatie\QueryBuilder\AllowedFilter;

class ContractorController extends Controller
{
    use HasIndexQuery;
    use HasActivityLogging;

    protected int $defaultPerPage = 15;

    private ExportService $exportService;

    public function __construct()
    {
        $this->modelClass  = Contractor::class;
        $this->defaultWith = ['tags', 'registryConfirmations'];

        $this->filters = [
            AllowedFilter::custom('search', new ComboSearchFilter(['name', 'vatId', 'taxId', 'regon', 'email', 'phone', 'description'])),
            AllowedFilter::custom('id', new AdvancedFilter()),
            AllowedFilter::custom('name', new AdvancedFilter()),
            AllowedFilter::custom('type', new AdvancedFilter(), 'type'),
            AllowedFilter::custom('taxId', new AdvancedFilter(), 'tax_id'),
            AllowedFilter::custom('vatId', new AdvancedFilter(), 'vat_id'),
            AllowedFilter::custom('regon', new AdvancedFilter(), 'regon'),
            AllowedFilter::custom('email', new AdvancedFilter()),
            AllowedFilter::custom('phone', new AdvancedFilter()),
            AllowedFilter::custom('website', new AdvancedFilter()),
            AllowedFilter::custom('country', new AdvancedFilter()),
            AllowedFilter::custom('description', new AdvancedFilter()),
            AllowedFilter::custom('isActive', new AdvancedFilter(['is_active' => 'boolean']), 'is_active'),
            AllowedFilter::custom('isBuyer', new AdvancedFilter(['is_buyer' => 'boolean']), 'is_buyer'),
            AllowedFilter::custom('isSupplier', new AdvancedFilter(['is_supplier' => 'boolean']), 'is_supplier'),
            AllowedFilter::custom('createdAt', new AdvancedFilter(), 'created_at'),
            AllowedFilter::custom('updatedAt', new AdvancedFilter(), 'updated_at'),
        ];

        $this->sorts = [
            'name',
            'type',
            'email',
            'country',
            'isActive'  => 'is_active',
            'createdAt' => 'created_at',
            'updatedAt' => 'updated_at',
        ];

        $this->defaultSort   = '-created_at';
        $this->exportService = app(ExportService::class);
    }

    public function index(SearchContractorRequest $request): AnonymousResourceCollection
    {
        $contractors = $this->getIndexPaginator($request);

        return ContractorResource::collection($contractors['data'])
            ->additional(['meta' => $contractors['meta']])
        ;
    }

    public function store(StoreContractorRequest $request, LogoFetcherService $logoFetcherService): ContractorResource
    {
        $validated  = $request->validated();

        $contractor = Contractor::create($validated['contractor']);

        if (isset($validated['registry_confirmation'])) {
            // TODO: Implement registry confirmation backend logic
            // $result = $this->autoFillService->autoFill($vatId, $regon, $country, $force);
            $confirmations = collect($validated['registry_confirmation'])->filter(fn ($item) => $item)->map(fn ($item, $key) => [
                'type'       => $key,
                'payload'    => $item,
                'result'     => $item,
                'success'    => true,
                'checked_at' => now(),
            ])->toArray();
            $contractor->registryConfirmations()->createMany($confirmations);
        }

        if (isset($validated['address'])) {
            $contractor->addresses()->create($validated['address']);
        }

        if (isset($validated['bank_account'])) {
            $contractor->bankAccounts()->create($validated['bank_account']);
        }

        $contractor->logModelActivity(ContractorActivityType::Created->value, $contractor);

        if ($this->shouldFetchLogo($contractor, $request)) {
            $logoFetcherService->fetchAndStore($contractor, $contractor->website, $contractor->email);
        }

        return new ContractorResource($contractor);
    }

    public function show(Contractor $contractor): ContractorResource
    {
        $contractor->load('preferences');

        return new ContractorResource($contractor);
    }

    public function update(UpdateContractorRequest $request, Contractor $contractor, LogoFetcherService $logoFetcherService): ContractorResource
    {
        $contractor->update($request->validated('contractor'));
        $contractor->logModelActivity(ContractorActivityType::Updated->value, $contractor);

        if ($this->shouldFetchLogo($contractor, $request)) {
            $logoFetcherService->fetchAndStore($contractor, $contractor->website, $contractor->email);
        }

        return new ContractorResource($contractor);
    }

    public function destroy(Contractor $contractor): JsonResponse
    {
        $contractor->logModelActivity(ContractorActivityType::Deleted->value, $contractor);
        $contractor->delete();

        return response()->json(['message' => 'Contractor deleted successfully.'], Response::HTTP_NO_CONTENT);
    }

    public function search(Request $request): JsonResponse|AnonymousResourceCollection
    {
        $query   = $request->input('q');
        $perPage = $request->input('perPage', $this->defaultPerPage);

        if (!$query) {
            return response()->json(['message' => 'Search query is required'], Response::HTTP_BAD_REQUEST);
        }

        $results = Contractor::search($query)
            ->query(function ($builder) use ($request) {
                return $this->getIndexQuery($request);
            })
            ->paginate($perPage)
        ;

        return ContractorResource::collection($results);
    }

    protected function shouldFetchLogo(Contractor $contractor, Request $request): bool
    {
        if ($contractor->hasMedia('logo')) {
            return false;
        }

        if (false === $request->boolean('options.fetchLogo', false)) {
            return false;
        }

        if ($request->has('contractor.website') || $request->has('contractor.email')) {
            return true;
        }

        return false;
    }

    /**
     * Export contractors as Excel file.
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
            ContractorsExport::class,
            $config,
            'contractors.xlsx'
        );
    }
}
