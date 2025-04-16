<?php

namespace App\Domain\Products\Controllers;

use App\Domain\Products\DTO\ProductDTO;
use App\Domain\Products\Models\Product;
use App\Domain\Products\Requests\ProductRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::with(['unit', 'vatRate'])->paginate();
        return response()->json(
            ProductDTO::collect($products)
        );
    }

    public function store(ProductRequest $request): JsonResponse
    {
        $dto = ProductDTO::from($request->validated());
        $product = Product::create((array) $dto);

        return response()->json(
            ProductDTO::from($product),
            Response::HTTP_CREATED
        );
    }

    public function show(Product $product): JsonResponse
    {
        $product->load(['unit', 'vatRate']);
        return response()->json(
            ProductDTO::from($product)
        );
    }

    public function update(ProductRequest $request, Product $product): JsonResponse
    {
        $dto = ProductDTO::from($request->validated());
        $product->update((array) $dto);

        return response()->json(
            ProductDTO::from($product)
        );
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
