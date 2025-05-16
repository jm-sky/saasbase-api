<?php

namespace App\Domain\Products\Controllers;

use App\Domain\Common\DTOs\TagDTO;
use App\Domain\Common\Requests\TagRequest;
use App\Domain\Products\Enums\ProductActivityType;
use App\Domain\Products\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ProductTagsController extends Controller
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

        activity()
            ->performedOn($product)
            ->withProperties([
                'tenant_id'  => request()->user()->tenant_id,
                'product_id' => $product->id,
                'tag'        => $tag->name,
            ])
            ->event(ProductActivityType::TagAdded->value)
            ->log('Product tag added')
        ;

        return response()->json([
            'message' => 'Tag added successfully.',
            'data'    => TagDTO::fromModel($tag)->toArray(),
        ], Response::HTTP_CREATED);
    }

    public function destroy(Product $product, string $tag): JsonResponse
    {
        activity()
            ->performedOn($product)
            ->withProperties([
                'tenant_id'  => request()->user()->tenant_id,
                'product_id' => $product->id,
                'tag'        => $tag,
            ])
            ->event(ProductActivityType::TagRemoved->value)
            ->log('Product tag removed')
        ;

        $product->removeTag($tag, $product->tenant_id);

        return response()->json(['message' => 'Tag removed successfully.'], Response::HTTP_NO_CONTENT);
    }

    public function sync(TagRequest $request, Product $product): JsonResponse
    {
        $tags = $request->input('tags', []);
        $product->syncTags($tags, $product->tenant_id);

        activity()
            ->performedOn($product)
            ->withProperties([
                'tenant_id'  => request()->user()->tenant_id,
                'product_id' => $product->id,
                'tags'       => $tags,
            ])
            ->event(ProductActivityType::TagsSynced->value)
            ->log('Product tags synced')
        ;

        return response()->json([
            'message' => 'Tags synced successfully.',
            'data'    => $product->getTagNames(),
        ]);
    }
}
