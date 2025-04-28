<?php

namespace App\Domain\Feeds\Controllers;

use App\Domain\Common\Models\Comment;
use App\Domain\Feeds\Models\Feed;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Controller for managing feed comments.
 */
class FeedCommentController extends Controller
{
    public function index(Feed $feed): AnonymousResourceCollection
    {
        $comments = $feed->comments()
            ->with('user')
            ->latest()
            ->paginate(10);

        return CommentResource::collection($comments);
    }

    public function store(Request $request, Feed $feed): CommentResource
    {
        $validated = $request->validate([
            'content' => ['required', 'string'],
        ]);

        $comment = $feed->comments()->create([
            'tenant_id' => tenant()->id,
            'user_id' => Auth::id(),
            'content' => $validated['content'],
        ]);

        return new CommentResource($comment);
    }

    public function destroy(Feed $feed, Comment $comment): Response
    {
        $this->authorize('delete', $comment);

        if ($comment->commentable_id !== $feed->id || $comment->commentable_type !== Feed::class) {
            abort(404); // Nie pozwalamy usuwać cudzych komentarzy lub innych modeli
        }

        $comment->delete();

        return response()->noContent();
    }
}
