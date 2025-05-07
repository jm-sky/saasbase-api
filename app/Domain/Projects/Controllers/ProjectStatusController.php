<?php

namespace App\Domain\Projects\Controllers;

use App\Domain\Common\Filters\DateRangeFilter;
use App\Domain\Common\Traits\HasIndexQuery;
use App\Domain\Projects\DTOs\ProjectStatusDTO;
use App\Domain\Projects\Models\ProjectStatus;
use App\Domain\Projects\Requests\ProjectStatusRequest;
use App\Domain\Projects\Requests\SearchProjectStatusRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
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
        $dto    = ProjectStatusDTO::from($request->validated());
        $status = ProjectStatus::create((array) $dto);

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
        $dto = ProjectStatusDTO::from($request->validated());
        $projectStatus->update((array) $dto);

        return response()->json(['data' => ProjectStatusDTO::from($projectStatus)]);
    }

    public function destroy(ProjectStatus $projectStatus): JsonResponse
    {
        $projectStatus->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
