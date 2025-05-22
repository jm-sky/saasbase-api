<?php

namespace App\Domain\Products\Controllers;

use App\Domain\Common\Filters\AdvancedFilter;
use App\Domain\Common\Filters\ComboSearchFilter;
use App\Domain\Common\Filters\DateRangeFilter;
use App\Domain\Common\Traits\HasActivityLogging;
use App\Domain\Common\Traits\HasIndexQuery;
use App\Domain\Products\Enums\ProductActivityType;
use App\Domain\Products\Models\Product;
use App\Domain\Products\Requests\ProductRequest;
use App\Domain\Products\Requests\SearchProductRequest;
use App\Domain\Products\Resources\ProductResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ProductController extends Controller
{
    use HasIndexQuery;
    use HasActivityLogging;

    protected int $defaultPerPage = 15;

    public function __construct()
    {
        $this->modelClass  = Product::class;
        $this->defaultWith = ['tags', 'unit', 'vatRate'];

        $this->filters = [
            AllowedFilter::custom('search', new ComboSearchFilter(['name', 'description'])),
            AllowedFilter::custom('name', new AdvancedFilter()),
            AllowedFilter::custom('description', new AdvancedFilter()),
            AllowedFilter::custom('unitId', new AdvancedFilter(), 'unit_id'),
            AllowedFilter::custom('vatRateId', new AdvancedFilter(), 'vat_rate_id'),
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

    public function index(SearchProductRequest $request): AnonymousResourceCollection
    {
        $products = $this->getIndexPaginator($request);

        return ProductResource::collection($products['data'])
            ->additional(['meta' => $products['meta']])
        ;
    }

    public function store(ProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());
        $product->load(['unit', 'vatRate']);
        $product->logModelActivity(ProductActivityType::Created->value, $product);

        return response()->json([
            'message' => 'Product created successfully.',
            'data'    => new ProductResource($product),
        ], Response::HTTP_CREATED);
    }

    public function show(Product $product): ProductResource
    {
        $product->load(['unit', 'vatRate']);

        return new ProductResource($product);
    }

    public function update(ProductRequest $request, Product $product): JsonResponse
    {
        $product->update($request->validated());
        $product->load(['unit', 'vatRate']);
        $product = $product->fresh();
        $product->logModelActivity(ProductActivityType::Updated->value, $product);

        return response()->json([
            'message' => 'Product updated successfully.',
            'data'    => new ProductResource($product),
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->logModelActivity(ProductActivityType::Deleted->value, $product);
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully.'], Response::HTTP_NO_CONTENT);
    }

    protected function getIndexQuery(Request $request): QueryBuilder
    {
        return QueryBuilder::for($this->modelClass)
            ->allowedFilters($this->filters)
            ->allowedSorts($this->sorts)
            ->defaultSort($this->defaultSort)
            ->with(['unit', 'vatRate'])
        ;
    }
}
