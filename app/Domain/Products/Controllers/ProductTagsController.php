<?php

namespace App\Domain\Products\Controllers;

use App\Domain\Common\DTOs\TagDTO;
use App\Domain\Common\Requests\TagRequest;
use App\Domain\Products\Models\Product;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ProductTagsController
{
    public function index(Product $product): JsonResponse
    {
        $tags = $product->getTagNames();

        return response()->json([
            'data' => $tags,
        ]);
    }

    public function store(TagRequest $request, Product $product): JsonResponse
    {
        $tag = $product->addTag($request->input('tag'), $product->tenant_id);

        return response()->json([
            'data' => TagDTO::fromModel($tag)->toArray(),
        ], Response::HTTP_CREATED);
    }

    public function destroy(Product $product, string $tag): JsonResponse
    {
        $product->removeTag($tag, $product->tenant_id);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function sync(TagRequest $request, Product $product): JsonResponse
    {
        $tags = $request->input('tags', []);
        $product->syncTags($tags, $product->tenant_id);

        return response()->json([
            'data' => $product->getTagNames(),
        ]);
    }
}
