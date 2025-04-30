<?php

namespace App\Domain\Projects\Controllers;

use App\Domain\Common\Concerns\HasIndexQuery;
use App\Domain\Common\Filters\DateRangeFilter;
use App\Domain\Projects\DTOs\ProjectDTO;
use App\Domain\Projects\Models\Project;
use App\Domain\Projects\Requests\CreateProjectRequest;
use App\Domain\Projects\Requests\UpdateProjectRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Symfony\Component\HttpFoundation\Response;

class ProjectController extends Controller
{
    use HasIndexQuery;

    protected int $defaultPerPage = 15;

    public function __construct()
    {
        $this->modelClass = Project::class;

        $this->filters = [
            AllowedFilter::partial('name'),
            AllowedFilter::partial('description'),
            AllowedFilter::exact('status_id'),
            AllowedFilter::exact('owner_id'),
            AllowedFilter::custom('createdAt', new DateRangeFilter('created_at')),
            AllowedFilter::custom('updatedAt', new DateRangeFilter('updated_at')),
        ];

        $this->sorts = [
            'name',
            'status_id',
            'owner_id',
            'createdAt' => 'created_at',
            'updatedAt' => 'updated_at',
        ];

        $this->defaultSort = '-created_at';
    }

    public function index(Request $request): JsonResponse
    {
        $result         = $this->getIndexPaginator($request);
        $result['data'] = ProjectDTO::collect($result['data']);

        return response()->json($result);
    }

    public function store(CreateProjectRequest $request): JsonResponse
    {
        $dto     = ProjectDTO::from($request->validated());
        $project = Project::create((array) $dto);

        return response()->json(
            ProjectDTO::fromModel($project),
            Response::HTTP_CREATED
        );
    }

    public function show(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        return response()->json(ProjectDTO::fromModel($project, true));
    }

    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $project->update($request->validated());

        return response()->json(ProjectDTO::fromModel($project));
    }

    public function destroy(Project $project): JsonResponse
    {
        $this->authorize('delete', $project);

        $project->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
