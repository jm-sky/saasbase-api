<?php

namespace App\Domain\Skills\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Filters\AdvancedFilter;
use App\Domain\Common\Filters\ComboSearchFilter;
use App\Domain\Common\Filters\DateRangeFilter;
use App\Domain\Common\Traits\HasIndexQuery;
use App\Domain\Rights\Enums\RoleName;
use App\Domain\Rights\Support\TenantScopedRoles;
use App\Domain\Skills\DTOs\SkillDTO;
use App\Domain\Skills\Models\Skill;
use App\Domain\Skills\Requests\SkillRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
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
            AllowedFilter::custom('name', new AdvancedFilter),
            AllowedFilter::custom('description', new AdvancedFilter),
            AllowedFilter::custom('skillCategoryId', new AdvancedFilter, 'skill_category_id'),
            AllowedFilter::custom('createdAt', new DateRangeFilter('created_at')),
            AllowedFilter::custom('updatedAt', new DateRangeFilter('updated_at')),
        ];

        $this->sorts = [
            'name',
            'description',
            'skillCategoryId' => 'skill_category_id',
            'createdAt' => 'created_at',
            'updatedAt' => 'updated_at',
        ];

        $this->defaultSort = '-created_at';
    }

    public function index(Request $request): JsonResponse
    {
        $result = $this->getIndexPaginator($request);
        $result['data'] = SkillDTO::collect($result['data']);

        return response()->json($result);
    }

    public function store(SkillRequest $request): JsonResponse
    {
        $this->authorizeManage();

        $dto = SkillDTO::from($request->validated());
        $skill = Skill::create((array) $dto);

        return response()->json(
            ['data' => SkillDTO::from($skill)],
            Response::HTTP_CREATED
        );
    }

    public function show(Skill $skill): JsonResponse
    {
        $skill->load('skillCategory');

        return response()->json(['data' => SkillDTO::from($skill)]);
    }

    public function update(SkillRequest $request, Skill $skill): JsonResponse
    {
        $this->authorizeManage();

        $dto = SkillDTO::from($request->validated());
        $skill->update((array) $dto);

        return response()->json(['data' => SkillDTO::from($skill)]);
    }

    public function destroy(Skill $skill): JsonResponse
    {
        $this->authorizeManage();

        $skill->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Skill/SkillCategory are global tables shared by every tenant, with
     * skills/user_skill/project_required_skills cascading on delete —
     * previously any authenticated user of any tenant could edit or nuke
     * data relied on by every other tenant. Restricted to Owner/Admin,
     * same as other broad/destructive actions in this codebase.
     */
    private function authorizeManage(): void
    {
        /** @var User $user */
        $user = Auth::user();
        $tenantId = $user->getTenantId();

        abort_unless(
            $tenantId && TenantScopedRoles::userHasAnyRole($user, $tenantId, [RoleName::Owner->value, RoleName::Admin->value]),
            Response::HTTP_FORBIDDEN
        );
    }
}
