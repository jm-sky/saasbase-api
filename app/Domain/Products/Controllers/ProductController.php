<?php

namespace App\Domain\Products\Controllers;

use App\Domain\Products\DTOs\ProductDTO;
use App\Domain\Products\Models\Product;
use App\Domain\Products\Requests\ProductRequest;
use App\Domain\Products\Requests\SearchProductRequest;
use App\Filters\DateRangeFilter;
use App\Http\Controllers\Concerns\HasIndexQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\QueryBuilder\AllowedFilter;

class ProductController extends Controller
{
    use HasIndexQuery;

    protected string $modelClass = Product::class;
    protected array $filters = [];
    protected array $sorts = [];

    public function __construct()
    {
        $this->filters = [
            'name',
            'description',
            'unit_id',
            'vat_rate_id',
            AllowedFilter::custom('created_at', new DateRangeFilter('created_at')),
            AllowedFilter::custom('updated_at', new DateRangeFilter('updated_at')),
        ];

        $this->sorts = [
            'name',
            'created_at',
            'updated_at',
        ];
    }

    public function index(SearchProductRequest $request): JsonResponse
    {
        $products = $this->getIndexQuery($request)
            ->where('tenant_id', $request->user()->getTenantId())
            ->with(['unit', 'vatRate'])
            ->paginate();

        return response()->json([
            'data' => ProductDTO::collect($products->items()),
            'meta' => [
                'currentPage' => $products->currentPage(),
                'lastPage' => $products->lastPage(),
                'perPage' => $products->perPage(),
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
}
