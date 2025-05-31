<?php

namespace App\Domain\Feeds\Controllers;

use App\Domain\Common\Traits\HasIndexQuery;
use App\Domain\Feeds\Models\Feed;
use App\Domain\Feeds\Requests\SearchFeedRequest;
use App\Domain\Feeds\Requests\StoreFeedRequest;
use App\Domain\Feeds\Resources\FeedResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * Controller for managing user feeds.
 */
class FeedController extends Controller
{
    use HasIndexQuery;

    public function __construct()
    {
        $this->modelClass  = Feed::class;
        $this->defaultWith = ['user'];

        $this->sorts = [
            'createdAt' => 'created_at',
            'updatedAt' => 'updated_at',
        ];

        $this->defaultSort = '-created_at';
    }

    public function getIndexQuery(Request $request): QueryBuilder
    {
        return parent::getIndexQuery($request)
            ->withCount('comments')
        ;
    }

    public function index(SearchFeedRequest $request): AnonymousResourceCollection
    {
        $feeds = $this->getIndexPaginator($request);

        return FeedResource::collection($feeds['data'])
            ->additional(['meta' => $feeds['meta']])
        ;
    }

    public function store(StoreFeedRequest $request): FeedResource
    {
        $feed = Feed::create([
            'tenant_id' => $request->user()->getTenantId(),
            'user_id'   => $request->user()->id,
            'title'     => $request->input('title'),
            'content'   => $request->input('content'),
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
