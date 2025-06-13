<?php

namespace App\Domain\Common\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Filters\AdvancedFilter;
use App\Domain\Common\Filters\ComboSearchFilter;
use App\Domain\Common\Models\Tag;
use App\Domain\Common\Resources\TagResource;
use App\Domain\Common\Traits\HasIndexQuery;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\AllowedFilter;
use Symfony\Component\HttpFoundation\Response;

class TagController extends Controller
{
    use HasIndexQuery;
    use AuthorizesRequests;

    protected int $defaultPerPage = 15;

    public function __construct()
    {
        $this->modelClass = Tag::class;

        $this->filters = [
            AllowedFilter::custom('search', new ComboSearchFilter(['name', 'slug'])),
            AllowedFilter::custom('name', new AdvancedFilter()),
            AllowedFilter::custom('slug', new AdvancedFilter()),
        ];

        $this->sorts = [
            'name',
            'slug',
            'createdAt' => 'created_at',
            'updatedAt' => 'updated_at',
        ];

        $this->defaultSort = 'name';
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $tags = $this->getIndexPaginator($request);

        return TagResource::collection($tags['data'])
            ->additional(['meta' => $tags['meta']])
        ;
    }

    public function store(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:tags,name'],
        ]);

        $tag = Tag::create([
            'tenant_id' => $user->tenant_id,
            'name'      => $request->name,
            'slug'      => Str::slug($request->name),
        ]);

        return response()->json(new TagResource($tag), Response::HTTP_CREATED);
    }
}
