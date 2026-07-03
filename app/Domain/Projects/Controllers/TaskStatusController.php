<?php

namespace App\Domain\Projects\Controllers;

use App\Domain\Common\Filters\DateRangeFilter;
use App\Domain\Common\Traits\HasIndexQuery;
use App\Domain\Projects\DTOs\TaskStatusDTO;
use App\Domain\Projects\Models\TaskStatus;
use App\Domain\Projects\Requests\SearchTaskStatusRequest;
use App\Domain\Projects\Requests\TaskStatusRequest;
use App\Domain\Rights\Enums\RoleName;
use App\Domain\Rights\Support\TenantScopedRoles;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;

class TaskStatusController extends Controller
{
    use HasIndexQuery;

    protected int $defaultPerPage = 15;

    public function __construct()
    {
        $this->modelClass = TaskStatus::class;

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

    public function index(SearchTaskStatusRequest $request): JsonResponse
    {
        $result         = $this->getIndexPaginator($request);
        $result['data'] = TaskStatusDTO::collect($result['data']);

        return response()->json($result);
    }

    public function store(TaskStatusRequest $request): JsonResponse
    {
        $this->authorizeManage();

        $dto    = TaskStatusDTO::from($request->validated());
        $status = TaskStatus::create($dto->toDbArray());

        return response()->json(
            ['data' => TaskStatusDTO::from($status)],
            Response::HTTP_CREATED
        );
    }

    public function show(TaskStatus $taskStatus): JsonResponse
    {
        return response()->json(['data' => TaskStatusDTO::from($taskStatus)]);
    }

    public function update(TaskStatusRequest $request, TaskStatus $taskStatus): JsonResponse
    {
        $this->authorizeManage();

        $dto = TaskStatusDTO::from($request->validated());
        $taskStatus->update($dto->toDbArray());

        return response()->json(['data' => TaskStatusDTO::from($taskStatus)]);
    }

    public function destroy(TaskStatus $taskStatus): JsonResponse
    {
        $this->authorizeManage();

        $taskStatus->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Task statuses are shared across every project/task in the tenant, so
     * changing them needs the same tenant-wide gate as ProjectStatusController.
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
