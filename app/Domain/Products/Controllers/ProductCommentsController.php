<?php

namespace App\Domain\Products\Controllers;

use App\Domain\Common\DTOs\CommentDTO;
use App\Domain\Common\Models\Comment;
use App\Domain\Common\Traits\HasActivityLogging;
use App\Domain\Products\Enums\ProductActivityType;
use App\Domain\Products\Models\Product;
use App\Domain\Products\Requests\ProductCommentRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ProductCommentsController extends Controller
{
    use HasActivityLogging;

    public function index(Product $product): JsonResponse
    {
        $comments = $product->comments()->with('user')->latest()->paginate();

        return response()->json([
            'data' => CommentDTO::collect($comments->items()),
            'meta' => [
                'currentPage'  => $comments->currentPage(),
                'lastPage'     => $comments->lastPage(),
                'perPage'      => $comments->perPage(),
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

        $product->logModelActivity(ProductActivityType::CommentCreated->value, $comment);

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
        $product->logModelActivity(ProductActivityType::CommentUpdated->value, $comment);

        return response()->json([
            'message' => 'Comment updated successfully.',
            'data'    => CommentDTO::fromModel($comment->fresh()),
        ]);
    }

    public function destroy(Product $product, Comment $comment): JsonResponse
    {
        abort_if($comment->commentable_id !== $product->id, Response::HTTP_NOT_FOUND);
        $product->logModelActivity(ProductActivityType::CommentDeleted->value, $comment);
        $comment->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
