<?php

namespace App\Domain\Skills\Controllers;

use App\Domain\Common\Filters\AdvancedFilter;
use App\Domain\Common\Filters\ComboSearchFilter;
use App\Domain\Common\Filters\DateRangeFilter;
use App\Domain\Common\Traits\HasIndexQuery;
use App\Domain\Skills\Models\SkillCategory;
use App\Domain\Skills\Requests\SkillCategoryRequest;
use App\Domain\Skills\Resources\SkillCategoryResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Spatie\QueryBuilder\AllowedFilter;

class SkillCategoryController extends Controller
{
    use HasIndexQuery;

    public function __construct()
    {
        $this->modelClass = SkillCategory::class;

        $this->filters = [
            AllowedFilter::custom('search', new ComboSearchFilter(['name', 'description'])),
            AllowedFilter::custom('name', new AdvancedFilter()),
            AllowedFilter::custom('description', new AdvancedFilter()),
            AllowedFilter::custom('createdAt', new DateRangeFilter('created_at')),
        ];

        $this->sorts = [
            'name',
            'description',
            'createdAt',
        ];
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $categories = $this->getIndexPaginator($request);

        return SkillCategoryResource::collection($categories['data'])
            ->additional(['meta' => $categories['meta']])
        ;
    }

    public function store(SkillCategoryRequest $request): SkillCategoryResource
    {
        $skillCategory = SkillCategory::create($request->validated());

        return new SkillCategoryResource($skillCategory);
    }

    public function show(SkillCategory $skillCategory): SkillCategoryResource
    {
        abort_if(!$skillCategory->exists(), Response::HTTP_NOT_FOUND);

        $skillCategory->load('skills');

        return new SkillCategoryResource($skillCategory);
    }

    public function update(SkillCategoryRequest $request, SkillCategory $skillCategory): SkillCategoryResource
    {
        abort_if(!$skillCategory->exists(), Response::HTTP_NOT_FOUND);

        $skillCategory->update($request->validated());

        return new SkillCategoryResource($skillCategory);
    }

    public function destroy(SkillCategory $skillCategory): JsonResponse
    {
        abort_if(!$skillCategory->exists(), Response::HTTP_NOT_FOUND);

        $skillCategory->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
