<?php

namespace App\Domain\Feeds\Controllers;

use App\Domain\Feeds\DTOs\FeedDTO;
use App\Domain\Feeds\Models\Feed;
use App\Domain\Feeds\Requests\StoreFeedRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * Controller for managing user feeds.
 */
class FeedController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $feeds = Feed::with('user')
            ->withCount('comments')
            ->latest()
            ->paginate(10)
        ;

        return response()->json([
            'data' => FeedDTO::collect($feeds),
        ]);
    }

    public function store(StoreFeedRequest $request): JsonResponse
    {
        $feed = Feed::create([
            'tenant_id' => $request->user()->getTenantId(),
            'user_id'   => Auth::id(),
            'title'     => $request->input('title'),
            'content'   => $request->input('content'),
        ]);

        return response()->json(FeedDTO::from($feed));
    }

    public function show(Feed $feed): JsonResponse
    {
        // TODO: Add authorization
        // $this->authorize('view', $feed);

        $feed->load(['user', 'comments.user']);

        return response()->json(FeedDTO::from($feed));
    }

    public function destroy(Feed $feed): Response
    {
        // TODO: Add authorization
        // $this->authorize('delete', $feed);

        $feed->delete();

        return response()->noContent();
    }
}
