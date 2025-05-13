<?php

namespace App\Domain\Contractors\Controllers;

use App\Domain\Common\Filters\AdvancedFilter;
use App\Domain\Common\Filters\ComboSearchFilter;
use App\Domain\Common\Filters\DateRangeFilter;
use App\Domain\Common\Traits\HasIndexQuery;
use App\Domain\Contractors\DTOs\ContractorDTO;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Contractors\Requests\ContractorRequest;
use App\Domain\Contractors\Requests\SearchContractorRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Spatie\QueryBuilder\AllowedFilter;

class ContractorController extends Controller
{
    use HasIndexQuery;

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

    public function index(SearchContractorRequest $request): JsonResponse
    {
        $result         = $this->getIndexPaginator($request);
        $result['data'] = ContractorDTO::collect($result['data']);

        return response()->json($result);
    }

    public function store(ContractorRequest $request): JsonResponse
    {
        $dto        = ContractorDTO::from($request->validated());
        $contractor = Contractor::create((array) $dto);

        return response()->json(
            ['data' => ContractorDTO::from($contractor)],
            Response::HTTP_CREATED
        );
    }

    public function show(Contractor $contractor): JsonResponse
    {
        return response()->json(['data' => ContractorDTO::from($contractor)]);
    }

    public function update(ContractorRequest $request, Contractor $contractor): JsonResponse
    {
        $dto = ContractorDTO::from($request->validated());
        $contractor->update((array) $dto);

        return response()->json(['data' => ContractorDTO::from($contractor)]);
    }

    public function destroy(Contractor $contractor): JsonResponse
    {
        $contractor->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
