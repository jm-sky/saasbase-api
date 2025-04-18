<?php

namespace App\Domain\Products\Controllers;

use App\Domain\Products\DTOs\ProductDTO;
use App\Domain\Products\Models\Product;
use App\Domain\Products\Requests\ProductRequest;
use App\Domain\Products\Requests\SearchProductRequest;
use App\Domain\Common\Filters\DateRangeFilter;
use App\Domain\Common\Concerns\HasIndexQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Builder;

class ProductController extends Controller
{
    use HasIndexQuery;

    private const PER_PAGE = 15;

    public function __construct()
    {
        $this->modelClass = Product::class;

        $this->filters = [
            AllowedFilter::exact('name'),
            AllowedFilter::exact('description'),
            AllowedFilter::exact('unitId', 'unit_id'),
            AllowedFilter::exact('vatRateId', 'vat_rate_id'),
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
    }

    public function index(SearchProductRequest $request): JsonResponse
    {
        $query = QueryBuilder::for($this->modelClass)
            ->allowedFilters($this->filters)
            ->allowedSorts($this->sorts)
            ->defaultSort($this->defaultSort)
            ->with(['unit', 'vatRate']);

        $products = $query->paginate($request->input('per_page', self::PER_PAGE));

        return response()->json([
            'data' => ProductDTO::collect($products->items()),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
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
        $product->load(['unit', 'vatRate']);
        return response()->json(
            ProductDTO::fromModel($product)
        );
    }

    public function update(ProductRequest $request, Product $product): JsonResponse
    {
        $product->update($request->validated());
        $product->load(['unit', 'vatRate']);
        $product = $product->fresh();

        return response()->json(
            ProductDTO::fromModel($product)
        );
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    protected function getIndexQuery(Request $request): QueryBuilder
    {
        return QueryBuilder::for($this->modelClass)
            ->allowedFilters($this->filters)
            ->allowedSorts($this->sorts)
            ->defaultSort($this->defaultSort)
            ->with(['unit', 'vatRate']);
    }
}
