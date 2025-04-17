<?php

namespace App\Domain\Products\Controllers;

use App\Domain\Products\DTOs\ProductDTO;
use App\Domain\Products\Models\Product;
use App\Domain\Products\Requests\ProductRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $products = Product::query()
            ->where('tenant_id', $request->user()->getTenantId())
            ->with(['unit', 'vatRate'])
            ->paginate();

        return response()->json(
            ProductDTO::collect($products->items())
        );
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
