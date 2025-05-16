<?php

namespace App\Domain\Products\Controllers;

use App\Domain\Common\DTOs\CommentDTO;
use App\Domain\Common\Models\Comment;
use App\Domain\Products\Enums\ProductActivityType;
use App\Domain\Products\Models\Product;
use App\Domain\Products\Requests\ProductCommentRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ProductCommentsController extends Controller
{
    public function index(Product $product): JsonResponse
    {
        $comments = $product->comments()->with('user')->latest()->paginate();

        return response()->json([
            'data' => CommentDTO::collect($comments->items()),
            'meta' => [
                'current_page' => $comments->currentPage(),
                'last_page'    => $comments->lastPage(),
                'per_page'     => $comments->perPage(),
                'total'        => $comments->total(),
            ],
        ]);
    }

    public function show(Product $product, Comment $comment): JsonResponse
    {
        abort_if($comment->commentable_id !== $product->id, Response::HTTP_NOT_FOUND);

        return response()->json([
            'data' => CommentDTO::fromModel($comment),
        ]);
    }

    public function store(ProductCommentRequest $request, Product $product): JsonResponse
    {
        $validated = $request->validated();
        $comment   = $product->comments()->create([
            'tenant_id'        => $product->tenant_id,
            'user_id'          => $request->user()->id,
            'content'          => $validated['content'],
        ]);

        activity()
            ->performedOn($product)
            ->withProperties([
                'tenant_id'  => request()->user()?->tenant_id,
                'product_id' => $product->id,
                'comment_id' => $comment->id,
            ])
            ->event(ProductActivityType::CommentCreated->value)
            ->log('Product comment created')
        ;

        return response()->json([
            'message' => 'Comment created successfully.',
            'data'    => CommentDTO::fromModel($comment),
        ], Response::HTTP_CREATED);
    }

    public function update(ProductCommentRequest $request, Product $product, Comment $comment): JsonResponse
    {
        abort_if($comment->commentable_id !== $product->id, Response::HTTP_NOT_FOUND);
        $validated = $request->validated();
        $comment->update($validated);

        activity()
            ->performedOn($product)
            ->withProperties([
                'tenant_id'  => request()->user()?->tenant_id,
                'product_id' => $product->id,
                'comment_id' => $comment->id,
            ])
            ->event(ProductActivityType::CommentUpdated->value)
            ->log('Product comment updated')
        ;

        return response()->json([
            'message' => 'Comment updated successfully.',
            'data'    => CommentDTO::fromModel($comment->fresh()),
        ]);
    }

    public function destroy(Product $product, Comment $comment): JsonResponse
    {
        abort_if($comment->commentable_id !== $product->id, Response::HTTP_NOT_FOUND);
        $comment->delete();

        activity()
            ->performedOn($product)
            ->withProperties([
                'tenant_id'  => request()->user()?->tenant_id,
                'product_id' => $product->id,
                'comment_id' => $comment->id,
            ])
            ->event(ProductActivityType::CommentDeleted->value)
            ->log('Product comment deleted')
        ;

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
