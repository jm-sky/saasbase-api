<?php

namespace App\Domain\Admin\Products\Controllers;

use App\Domain\Common\Filters\AdvancedFilter;
use App\Domain\Common\Filters\ComboSearchFilter;
use App\Domain\Common\Filters\DateRangeFilter;
use App\Domain\Common\Traits\HasIndexQuery;
use App\Domain\Products\DTOs\ProductDTO;
use App\Domain\Products\Models\Product;
use App\Domain\Products\Requests\ProductRequest;
use App\Domain\Products\Requests\SearchProductRequest;
use App\Domain\Tenant\Scopes\TenantScope;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Spatie\QueryBuilder\AllowedFilter;

class AdminProductController extends Controller
{
    use HasIndexQuery;

    protected int $defaultPerPage = 15;

    public function __construct()
    {
        $this->modelClass = Product::class;

        $this->withoutGlobalScopes = [TenantScope::class];

        $this->filters = [
            AllowedFilter::custom('search', new ComboSearchFilter(['name', 'description'])),
            AllowedFilter::custom('name', new AdvancedFilter()),
            AllowedFilter::custom('description', new AdvancedFilter()),
            AllowedFilter::custom('unitId', new AdvancedFilter(), 'unit_id'),
            AllowedFilter::custom('vatRateId', new AdvancedFilter(), 'vat_rate_id'),
            AllowedFilter::custom('tenantId', new AdvancedFilter(), 'tenant_id'),
            AllowedFilter::custom('createdAt', new DateRangeFilter('created_at')),
            AllowedFilter::custom('updatedAt', new DateRangeFilter('updated_at')),
        ];

        $this->sorts = [
            'name',
            'description',
            'createdAt' => 'created_at',
            'updatedAt' => 'updated_at',
        ];

        $this->defaultSort = '-created_at';
        $this->defaultWith = ['unit', 'vatRate'];
    }

    public function index(SearchProductRequest $request): JsonResponse
    {
        $result         = $this->getIndexPaginator($request);
        $result['data'] = ProductDTO::collect($result['data']);

        return response()->json($result);
    }

    public function store(ProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());
        $product->load(['unit', 'vatRate']);

        return response()->json(
            ProductDTO::fromModel($product),
            Response::HTTP_CREATED
        );
    }

    public function show(Product $product): JsonResponse
    {
        $product = Product::withoutGlobalScope(TenantScope::class)
            ->with(['unit', 'vatRate'])
            ->findOrFail($product->getKey())
        ;

        return response()->json(
            ProductDTO::fromModel($product)
        );
    }

    public function update(ProductRequest $request, Product $product): JsonResponse
    {
        $product = Product::withoutGlobalScope(TenantScope::class)
            ->findOrFail($product->getKey())
        ;

        $product->update($request->validated());
        $product->load(['unit', 'vatRate']);
        $product = $product->fresh();

        return response()->json(
            ProductDTO::fromModel($product)
        );
    }

    public function destroy(Product $product): JsonResponse
    {
        $product = Product::withoutGlobalScope(TenantScope::class)
            ->findOrFail($product->getKey())
        ;

        $product->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
