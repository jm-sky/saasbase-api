<?php

namespace App\Domain\Contractors\Controllers;

use App\Domain\Common\Filters\AdvancedFilter;
use App\Domain\Common\Filters\ComboSearchFilter;
use App\Domain\Common\Filters\DateRangeFilter;
use App\Domain\Common\Traits\HasActivityLogging;
use App\Domain\Common\Traits\HasIndexQuery;
use App\Domain\Contractors\Enums\ContractorActivityType;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Contractors\Requests\ContractorRequest;
use App\Domain\Contractors\Requests\SearchContractorRequest;
use App\Domain\Contractors\Resources\ContractorResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Spatie\QueryBuilder\AllowedFilter;

class ContractorController extends Controller
{
    use HasIndexQuery;
    use HasActivityLogging;

    protected int $defaultPerPage = 15;

    public function __construct()
    {
        $this->modelClass  = Contractor::class;
        $this->defaultWith = ['tags'];

        $this->filters = [
            AllowedFilter::custom('search', new ComboSearchFilter(['name', 'email', 'phone', 'notes'])),
            AllowedFilter::custom('name', new AdvancedFilter()),
            AllowedFilter::custom('email', new AdvancedFilter()),
            AllowedFilter::custom('phone', new AdvancedFilter()),
            AllowedFilter::custom('country', new AdvancedFilter()),
            AllowedFilter::custom('taxId', new AdvancedFilter(), 'tax_id'),
            AllowedFilter::custom('notes', new AdvancedFilter()),
            AllowedFilter::custom('isActive', new AdvancedFilter(['is_active' => 'boolean']), 'is_active'),
            AllowedFilter::custom('createdAt', new DateRangeFilter('created_at')),
            AllowedFilter::custom('updatedAt', new DateRangeFilter('updated_at')),
        ];

        $this->sorts = [
            'name',
            'email',
            'country',
            'isActive'  => 'is_active',
            'createdAt' => 'created_at',
            'updatedAt' => 'updated_at',
        ];

        $this->defaultSort = '-created_at';
    }

    public function index(SearchContractorRequest $request): AnonymousResourceCollection
    {
        $contractors = $this->getIndexPaginator($request);

        return ContractorResource::collection($contractors['data'])
            ->additional(['meta' => [
                'currentPage' => $contractors['current_page'],
                'lastPage'    => $contractors['last_page'],
                'perPage'     => $contractors['per_page'],
                'total'       => $contractors['total'],
            ]])
        ;
    }

    public function store(ContractorRequest $request): JsonResponse
    {
        $contractor = Contractor::create($request->validated());
        $contractor->logModelActivity(ContractorActivityType::Created->value, $contractor);

        return response()->json([
            'message' => 'Contractor created successfully.',
            'data'    => new ContractorResource($contractor),
        ], Response::HTTP_CREATED);
    }

    public function show(Contractor $contractor): ContractorResource
    {
        return new ContractorResource($contractor);
    }

    public function update(ContractorRequest $request, Contractor $contractor): JsonResponse
    {
        $contractor->update($request->validated());
        $contractor->logModelActivity(ContractorActivityType::Updated->value, $contractor);

        return response()->json([
            'message' => 'Contractor updated successfully.',
            'data'    => new ContractorResource($contractor),
        ]);
    }

    public function destroy(Contractor $contractor): JsonResponse
    {
        $contractor->logModelActivity(ContractorActivityType::Deleted->value, $contractor);
        $contractor->delete();

        return response()->json(['message' => 'Contractor deleted successfully.'], Response::HTTP_NO_CONTENT);
    }
}
