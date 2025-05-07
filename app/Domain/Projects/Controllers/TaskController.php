<?php

namespace App\Domain\Projects\Controllers;

use App\Domain\Common\Filters\AdvancedFilter;
use App\Domain\Common\Filters\ComboSearchFilter;
use App\Domain\Common\Filters\DateRangeFilter;
use App\Domain\Common\Traits\HasIndexQuery;
use App\Domain\Projects\DTOs\TaskDTO;
use App\Domain\Projects\Models\Task;
use App\Domain\Projects\Requests\CreateTaskRequest;
use App\Domain\Projects\Requests\UpdateTaskRequest;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends Controller
{
    use HasIndexQuery;
    use AuthorizesRequests;

    protected int $defaultPerPage = 15;

    public function __construct()
    {
        $this->modelClass = Task::class;

        $this->filters = [
            AllowedFilter::custom('search', new ComboSearchFilter(['title', 'description'])),
            AllowedFilter::custom('projectId', new AdvancedFilter(), 'project_id'),
            AllowedFilter::custom('statusId', new AdvancedFilter(), 'status_id'),
            AllowedFilter::custom('assignedToId', new AdvancedFilter(), 'assigned_to_id'),
            AllowedFilter::custom('priority', new AdvancedFilter()),
            AllowedFilter::custom('title', new AdvancedFilter()),
            AllowedFilter::custom('description', new AdvancedFilter()),
            AllowedFilter::custom('dueDate', new DateRangeFilter('due_date')),
            AllowedFilter::custom('createdAt', new DateRangeFilter('created_at')),
            AllowedFilter::custom('updatedAt', new DateRangeFilter('updated_at')),
        ];

        $this->sorts = [
            'title',
            'priority',
            'status_id',
            'dueDate'   => 'due_date',
            'createdAt' => 'created_at',
            'updatedAt' => 'updated_at',
        ];

        $this->defaultSort = '-created_at';
    }

    public function index(Request $request): JsonResponse
    {
        $result         = $this->getIndexPaginator($request);
        $result['data'] = TaskDTO::collect($result['data']);

        return response()->json($result);
    }

    public function store(CreateTaskRequest $request): JsonResponse
    {
        $task = Task::create([
            'tenant_id'      => Auth::user()->tenant_id,
            'project_id'     => $request->input('project_id'),
            'title'          => $request->input('title'),
            'description'    => $request->input('description'),
            'status_id'      => $request->input('status_id'),
            'priority'       => $request->input('priority'),
            'assigned_to_id' => $request->input('assigned_to_id'),
            'created_by_id'  => Auth::id(),
            'due_date'       => $request->input('due_date'),
        ]);

        return response()->json(
            ['data' => TaskDTO::fromModel($task)],
            Response::HTTP_CREATED
        );
    }

    public function show(Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        return response()->json(['data' => TaskDTO::fromModel($task)]);
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $task->update($request->validated());

        return response()->json(['data' => TaskDTO::fromModel($task)]);
    }

    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
