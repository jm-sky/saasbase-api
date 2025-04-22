<?php

namespace App\Domain\Contractors\Controllers;

use App\Domain\Common\Concerns\HasIndexQuery;
use App\Domain\Common\Filters\DateRangeFilter;
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
        $this->modelClass = Contractor::class;

        $this->filters = [
            AllowedFilter::partial('name'),
            AllowedFilter::partial('email'),
            AllowedFilter::partial('phone'),
            AllowedFilter::partial('country'),
            AllowedFilter::partial('taxId', 'tax_id'),
            AllowedFilter::partial('notes'),
            AllowedFilter::exact('isActive', 'is_active'),
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
            ContractorDTO::from($contractor),
            Response::HTTP_CREATED
        );
    }

    public function show(Contractor $contractor): JsonResponse
    {
        return response()->json(
            ContractorDTO::from($contractor)
        );
    }

    public function update(ContractorRequest $request, Contractor $contractor): JsonResponse
    {
        $dto = ContractorDTO::from($request->validated());
        $contractor->update((array) $dto);

        return response()->json(
            ContractorDTO::from($contractor)
        );
    }

    public function destroy(Contractor $contractor): JsonResponse
    {
        $contractor->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
