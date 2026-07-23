<?php

namespace App\Domain\Projects\Controllers;

use App\Domain\Common\Filters\DateRangeFilter;
use App\Domain\Common\Traits\HasIndexQuery;
use App\Domain\Projects\DTOs\ProjectStatusDTO;
use App\Domain\Projects\Models\ProjectStatus;
use App\Domain\Projects\Requests\ProjectStatusRequest;
use App\Domain\Projects\Requests\SearchProjectStatusRequest;
use App\Domain\Rights\Enums\RoleName;
use App\Domain\Rights\Support\TenantScopedRoles;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;

class ProjectStatusController extends Controller
{
    use HasIndexQuery;

    protected int $defaultPerPage = 15;

    public function __construct()
    {
        $this->modelClass = ProjectStatus::class;

        $this->filters = [
            AllowedFilter::partial('name'),
            AllowedFilter::exact('isDefault', 'is_default'),
            AllowedFilter::custom('createdAt', new DateRangeFilter('created_at')),
            AllowedFilter::custom('updatedAt', new DateRangeFilter('updated_at')),
        ];

        $this->sorts = [
            'name',
            'sortOrder' => 'sort_order',
            'isDefault' => 'is_default',
            'createdAt' => 'created_at',
            'updatedAt' => 'updated_at',
        ];

        $this->defaultSort = 'sort_order';
    }

    public function index(SearchProjectStatusRequest $request): JsonResponse
    {
        $result         = $this->getIndexPaginator($request);
        $result['data'] = ProjectStatusDTO::collect($result['data']);

        return response()->json($result);
    }

    public function store(ProjectStatusRequest $request): JsonResponse
    {
        $this->authorizeManage();

        $dto    = ProjectStatusDTO::from($request->validated());
        $status = ProjectStatus::create($dto->toDbArray());

        return response()->json(
            ['data' => ProjectStatusDTO::from($status)],
            Response::HTTP_CREATED
        );
    }

    public function show(ProjectStatus $projectStatus): JsonResponse
    {
        return response()->json(['data' => ProjectStatusDTO::from($projectStatus)]);
    }

    public function update(ProjectStatusRequest $request, ProjectStatus $projectStatus): JsonResponse
    {
        $this->authorizeManage();

        $dto = ProjectStatusDTO::from($request->validated());
        $projectStatus->update($dto->toDbArray());

        return response()->json(['data' => ProjectStatusDTO::from($projectStatus)]);
    }

    public function destroy(ProjectStatus $projectStatus): JsonResponse
    {
        $this->authorizeManage();

        $projectStatus->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Project statuses are shared across every project in the tenant, so
     * changing them needs to be gated the same way TaskController::export()
     * gates bulk/tenant-wide actions.
     */
    private function authorizeManage(): void
    {
        /** @var \App\Domain\Auth\Models\User $user */
        $user     = Auth::user();
        $tenantId = $user->getTenantId();

        abort_unless(
            $tenantId && TenantScopedRoles::userHasAnyRole($user, $tenantId, [RoleName::Owner->value, RoleName::Admin->value]),
            Response::HTTP_FORBIDDEN
        );
    }
}
