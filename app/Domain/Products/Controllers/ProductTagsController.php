<?php

namespace App\Domain\Products\Controllers;

use App\Domain\Common\DTOs\TagDTO;
use App\Domain\Common\Requests\TagRequest;
use App\Domain\Common\Traits\HasActivityLogging;
use App\Domain\Products\Enums\ProductActivityType;
use App\Domain\Products\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ProductTagsController extends Controller
{
    use HasActivityLogging;

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
        $product->logModelActivity(ProductActivityType::TagAdded->value, $tag, ['tag' => $tag->name]);

        return response()->json([
            'message' => 'Tag added successfully.',
            'data'    => TagDTO::fromModel($tag)->toArray(),
        ], Response::HTTP_CREATED);
    }

    public function destroy(Product $product, string $tag): JsonResponse
    {
        $product->logModelActivity(ProductActivityType::TagRemoved->value, null, ['tag' => $tag]);
        $product->removeTag($tag, $product->tenant_id);

        return response()->json(['message' => 'Tag removed successfully.'], Response::HTTP_NO_CONTENT);
    }

    public function sync(TagRequest $request, Product $product): JsonResponse
    {
        $tags = $request->input('tags', []);
        $product->syncTags($tags, $product->tenant_id);
        $product->logModelActivity(ProductActivityType::TagsSynced->value, null, ['tags' => $tags]);

        return response()->json([
            'message' => 'Tags synced successfully.',
            'data'    => $product->getTagNames(),
        ]);
    }
}
