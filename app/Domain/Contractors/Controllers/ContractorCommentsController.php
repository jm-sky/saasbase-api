<?php

namespace App\Domain\Contractors\Controllers;

use App\Domain\Common\DTOs\CommentDTO;
use App\Domain\Common\Models\Comment;
use App\Domain\Contractors\Enums\ContractorActivityType;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Contractors\Requests\ContractorCommentRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ContractorCommentsController extends Controller
{
    public function index(Contractor $contractor): JsonResponse
    {
        $comments = $contractor->comments()->with('user')->latest()->paginate();

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

    public function show(Contractor $contractor, Comment $comment): JsonResponse
    {
        abort_if($comment->commentable_id !== $contractor->id, Response::HTTP_NOT_FOUND);

        return response()->json([
            'data' => CommentDTO::fromModel($comment),
        ]);
    }

    public function store(ContractorCommentRequest $request, Contractor $contractor): JsonResponse
    {
        $validated = $request->validated();
        $comment   = $contractor->comments()->create([
            'tenant_id'        => $contractor->tenant_id,
            'user_id'          => $request->user()->id,
            'content'          => $validated['content'],
        ]);

        activity()
            ->performedOn($contractor)
            ->withProperties([
                'tenant_id'     => request()->user()->tenant_id,
                'contractor_id' => $contractor->id,
                'comment_id'    => $comment->id,
            ])
            ->event(ContractorActivityType::CommentCreated->value)
            ->log('Contractor comment created')
        ;

        return response()->json([
            'message' => 'Comment created successfully.',
            'data'    => CommentDTO::fromModel($comment),
        ], Response::HTTP_CREATED);
    }

    public function update(ContractorCommentRequest $request, Contractor $contractor, Comment $comment): JsonResponse
    {
        abort_if($comment->commentable_id !== $contractor->id, Response::HTTP_NOT_FOUND);
        $validated = $request->validated();
        $comment->update($validated);

        activity()
            ->performedOn($contractor)
            ->withProperties([
                'tenant_id'     => request()->user()->tenant_id,
                'contractor_id' => $contractor->id,
                'comment_id'    => $comment->id,
            ])
            ->event(ContractorActivityType::CommentUpdated->value)
            ->log('Contractor comment updated')
        ;

        return response()->json([
            'message' => 'Comment updated successfully.',
            'data'    => CommentDTO::fromModel($comment->fresh()),
        ]);
    }

    public function destroy(Contractor $contractor, Comment $comment): JsonResponse
    {
        abort_if($comment->commentable_id !== $contractor->id, Response::HTTP_NOT_FOUND);
        $comment->delete();

        activity()
            ->performedOn($contractor)
            ->withProperties([
                'tenant_id'     => request()->user()->tenant_id,
                'contractor_id' => $contractor->id,
                'comment_id'    => $comment->id,
            ])
            ->event(ContractorActivityType::CommentDeleted->value)
            ->log('Contractor comment deleted')
        ;

        return response()->json(['message' => 'Comment deleted successfully.'], Response::HTTP_NO_CONTENT);
    }
}
