<?php

namespace App\Domain\Feeds\Controllers;

use App\Domain\Common\Traits\HasIndexQuery;
use App\Domain\Feeds\Models\Feed;
use App\Domain\Feeds\Requests\SearchFeedRequest;
use App\Domain\Feeds\Requests\StoreFeedRequest;
use App\Domain\Feeds\Resources\FeedResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

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

    public function index(SearchFeedRequest $request): AnonymousResourceCollection
    {
        $query = $this->getIndexQuery($request);
        $query = $query->withCount('comments');
        $feeds = $this->getIndexPaginator($request, query: $query);

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

        if ($request->hasFile('attachments')) {
            $attachments = $request->file('attachments');

            foreach ($attachments as $attachment) {
                $feed->addMedia($attachment)
                    ->toMediaCollection('attachments')
                ;
            }
        }

        $feed->load(['user']);
        $feed->loadCount('comments');

        return new FeedResource($feed);
    }

    public function show(Feed $feed): FeedResource
    {
        $feed->load(['user', 'comments.user']);
        $feed->loadCount('comments');

        return new FeedResource($feed);
    }

    public function destroy(Feed $feed): Response
    {
        $feed->delete();

        return response()->noContent();
    }
}
