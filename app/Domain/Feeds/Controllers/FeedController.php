<?php

namespace App\Domain\Feeds\Controllers;

use App\Domain\Feeds\Models\Feed;
use App\Domain\Feeds\Requests\StoreFeedRequest;
use App\Domain\Feeds\Resources\FeedResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Controllers\Controller;

/**
 * Controller for managing user feeds.
 */
class FeedController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $feeds = Feed::with('user')
            ->withCount('comments')
            ->latest()
            ->paginate(10);

        return FeedResource::collection($feeds);
    }

    public function store(StoreFeedRequest $request): FeedResource
    {
        $feed = Feed::create([
            'tenant_id' => tenant()->id,
            'user_id' => Auth::id(),
            'title' => $request->input('title'),
            'content' => $request->input('content'),
        ]);

        return new FeedResource($feed);
    }

    public function show(Feed $feed): FeedResource
    {
        $this->authorize('view', $feed);

        $feed->load(['user', 'comments.user']);

        return new FeedResource($feed);
    }

    public function destroy(Feed $feed): Response
    {
        $this->authorize('delete', $feed);

        $feed->delete();

        return response()->noContent();
    }
}
