<?php

namespace App\Domain\Projects\Controllers;

use App\Domain\Common\Filters\AdvancedFilter;
use App\Domain\Common\Filters\ComboSearchFilter;
use App\Domain\Common\Filters\DateRangeFilter;
use App\Domain\Common\Traits\HasIndexQuery;
use App\Domain\Export\DTOs\ExportConfigDTO;
use App\Domain\Export\Exports\TasksExport;
use App\Domain\Export\Services\ExportService;
use App\Domain\Projects\Models\Task;
use App\Domain\Projects\Requests\CreateTaskRequest;
use App\Domain\Projects\Requests\UpdateTaskRequest;
use App\Domain\Projects\Resources\TaskResource;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends Controller
{
    use HasIndexQuery;
    use AuthorizesRequests;

    protected int $defaultPerPage = 15;

    private ExportService $exportService;

    public function __construct()
    {
        $this->modelClass = Task::class;

        $this->filters = [
            AllowedFilter::custom('search', new ComboSearchFilter(['title', 'description'])),
            AllowedFilter::custom('projectId', new AdvancedFilter(), 'project_id'),
            AllowedFilter::custom('statusId', new AdvancedFilter(), 'status_id'),
            AllowedFilter::custom('assigneeId', new AdvancedFilter(), 'assignee_id'),
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

        $this->defaultSort   = '-created_at';
        $this->defaultWith   = ['assignee', 'status'];
        $this->exportService = app(ExportService::class);
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $result = $this->getIndexPaginator($request);

        return TaskResource::collection($result['data'])
            ->additional(['meta' => $result['meta']])
        ;
    }

    public function store(CreateTaskRequest $request): TaskResource
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

        return new TaskResource($task);
    }

    public function show(Task $task): TaskResource
    {
        $this->authorize('view', $task);

        return new TaskResource($task);
    }

    public function update(UpdateTaskRequest $request, Task $task): TaskResource
    {
        $this->authorize('update', $task);

        $task->update($request->validated());

        return new TaskResource($task);
    }

    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Export tasks as Excel file.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request)
    {
        $config = new ExportConfigDTO(
            filters: $request->all(),
            columns: $request->get('columns', []),
            formatting: $request->get('formatting', [])
        );

        return $this->exportService->download(
            TasksExport::class,
            $config,
            'tasks.xlsx'
        );
    }
}
