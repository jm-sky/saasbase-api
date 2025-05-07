<?php

namespace App\Domain\Skills\Controllers;

use App\Domain\Common\Filters\AdvancedFilter;
use App\Domain\Common\Filters\ComboSearchFilter;
use App\Domain\Common\Filters\DateRangeFilter;
use App\Domain\Common\Traits\HasIndexQuery;
use App\Domain\Skills\DTOs\SkillDTO;
use App\Domain\Skills\Models\Skill;
use App\Domain\Skills\Requests\SkillRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\QueryBuilder\AllowedFilter;

class SkillController extends Controller
{
    use HasIndexQuery;

    protected int $defaultPerPage = 15;
    // protected array $defaultWith = ['skillCategory'];

    public function __construct()
    {
        $this->modelClass = Skill::class;

        $this->filters = [
            AllowedFilter::custom('search', new ComboSearchFilter(['name', 'description'])),
            AllowedFilter::custom('name', new AdvancedFilter()),
            AllowedFilter::custom('description', new AdvancedFilter()),
            AllowedFilter::custom('skillCategoryId', new AdvancedFilter(), 'skill_category_id'),
            AllowedFilter::custom('createdAt', new DateRangeFilter('created_at')),
            AllowedFilter::custom('updatedAt', new DateRangeFilter('updated_at')),
        ];

        $this->sorts = [
            'name',
            'description',
            'skillCategoryId' => 'skill_category_id',
            'createdAt'       => 'created_at',
            'updatedAt'       => 'updated_at',
        ];

        $this->defaultSort = '-created_at';
    }

    public function index(Request $request): JsonResponse
    {
        $result         = $this->getIndexPaginator($request);
        $result['data'] = SkillDTO::collect($result['data']);

        return response()->json($result);
    }

    public function store(SkillRequest $request): JsonResponse
    {
        $dto   = SkillDTO::from($request->validated());
        $skill = Skill::create((array) $dto);

        return response()->json(
            SkillDTO::from($skill),
            Response::HTTP_CREATED
        );
    }

    public function show(Skill $skill): JsonResponse
    {
        $skill->load('skillCategory');

        return response()->json(
            SkillDTO::from($skill)
        );
    }

    public function update(SkillRequest $request, Skill $skill): JsonResponse
    {
        $dto = SkillDTO::from($request->validated());
        $skill->update((array) $dto);

        return response()->json(
            SkillDTO::from($skill)
        );
    }

    public function destroy(Skill $skill): JsonResponse
    {
        $skill->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
