<?php

namespace App\Domain\Feeds\Controllers;

use App\Domain\Common\DTOs\CommentDTO;
use App\Domain\Common\Models\Comment;
use App\Domain\Feeds\Models\Feed;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * Controller for managing feed comments.
 */
class FeedCommentController extends Controller
{
    public function index(Feed $feed): JsonResponse
    {
        $comments = $feed->comments()
            ->with('user')
            ->latest()
            ->paginate(10)
        ;

        return response()->json([
            'data' => CommentDTO::collect($comments),
        ]);
    }

    public function store(Request $request, Feed $feed): JsonResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string'],
        ]);

        $comment = $feed->comments()->create([
            'tenant_id' => $request->user()->getTenantId(),
            'user_id'   => Auth::id(),
            'content'   => $validated['content'],
        ]);

        return response()->json(CommentDTO::from($comment));
    }

    public function destroy(Feed $feed, Comment $comment): Response
    {
        if ($comment->commentable_id !== $feed->id || Feed::class !== $comment->commentable_type) {
            abort(404); // Nie pozwalamy usuwać cudzych komentarzy lub innych modeli
        }

        abort_unless(Auth::id() === $comment->user_id, Response::HTTP_FORBIDDEN);

        $comment->delete();

        return response()->noContent();
    }
}
