<?php

namespace App\Domain\Products\Controllers;

use App\Domain\Common\Filters\AdvancedFilter;
use App\Domain\Common\Filters\ComboSearchFilter;
use App\Domain\Common\Traits\HasActivityLogging;
use App\Domain\Common\Traits\HasIndexQuery;
use App\Domain\Export\DTOs\ExportConfigDTO;
use App\Domain\Export\Exports\ProductsExport;
use App\Domain\Export\Services\ExportService;
use App\Domain\Products\Enums\ProductActivityType;
use App\Domain\Products\Models\Product;
use App\Domain\Products\Requests\ProductRequest;
use App\Domain\Products\Requests\SearchProductRequest;
use App\Domain\Products\Resources\ProductLookupResource;
use App\Domain\Products\Resources\ProductResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Spatie\QueryBuilder\AllowedFilter;

class ProductController extends Controller
{
    use HasIndexQuery;
    use HasActivityLogging;

    protected int $defaultPerPage = 15;

    private ExportService $exportService;

    public function __construct()
    {
        $this->modelClass  = Product::class;
        $this->defaultWith = ['tags', 'unit', 'vatRate'];

        $this->filters = [
            AllowedFilter::custom('search', new ComboSearchFilter(['name', 'description'])),
            AllowedFilter::custom('name', new AdvancedFilter()),
            AllowedFilter::custom('type', new AdvancedFilter()),
            AllowedFilter::custom('description', new AdvancedFilter()),
            AllowedFilter::custom('unitId', new AdvancedFilter(), 'unit_id'),
            AllowedFilter::custom('vatRateId', new AdvancedFilter(), 'vat_rate_id'),
            AllowedFilter::custom('createdAt', new AdvancedFilter(), 'created_at'),
            AllowedFilter::custom('updatedAt', new AdvancedFilter(), 'updated_at'),
        ];

        $this->sorts = [
            'name',
            'description',
            'createdAt' => 'created_at',
            'updatedAt' => 'updated_at',
        ];

        $this->defaultSort   = '-created_at';
        $this->exportService = app(ExportService::class);
    }

    public function index(SearchProductRequest $request): AnonymousResourceCollection
    {
        $products = $this->getIndexPaginator($request);

        return ProductResource::collection($products['data'])
            ->additional(['meta' => $products['meta']])
        ;
    }

    public function lookup(SearchProductRequest $request): AnonymousResourceCollection
    {
        $products = $this->getIndexPaginator($request);

        return ProductLookupResource::collection($products['data'])
            ->additional(['meta' => $products['meta']])
        ;
    }

    public function store(ProductRequest $request): ProductResource
    {
        $product = Product::create($request->validated());
        $product->load(['unit', 'vatRate']);
        $product->logModelActivity(ProductActivityType::Created->value, $product);

        return new ProductResource($product);
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

    public function search(Request $request): JsonResponse|AnonymousResourceCollection
    {
        $query   = $request->input('q');
        $perPage = $request->input('perPage', $this->defaultPerPage);

        if (!$query) {
            return response()->json(['message' => 'Search query is required'], Response::HTTP_BAD_REQUEST);
        }

        $results = Product::search($query)
            ->query(function ($builder) use ($request) {
                return $this->getIndexQuery($request);
            })
            ->paginate($perPage)
        ;

        return ProductResource::collection($results);
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
            ProductsExport::class,
            $config,
            'products.xlsx'
        );
    }
}
