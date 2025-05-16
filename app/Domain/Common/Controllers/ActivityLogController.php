<?php

namespace App\Domain\Common\Controllers;

use App\Domain\Common\DTOs\ActivityLogDTO;
use App\Domain\Common\Filters\DateRangeFilter;
use App\Domain\Common\Models\Activity;
use App\Domain\Common\Traits\HasIndexQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;

class ActivityLogController extends Controller
{
    use HasIndexQuery;

    protected int $defaultPerPage = 15;

    public function __construct()
    {
        $this->modelClass  = Activity::class;
        $this->defaultWith = ['causer', 'subject'];

        $this->filters = [
            AllowedFilter::exact('subjectType', 'subject_type'),
            AllowedFilter::exact('subjectId', 'subject_id'),
            AllowedFilter::exact('causerType', 'causer_type'),
            AllowedFilter::exact('causerId', 'causer_id'),
            AllowedFilter::exact('event'),
            AllowedFilter::custom('createdAt', new DateRangeFilter('created_at')),
        ];

        $this->sorts = [
            'createdAt' => 'created_at',
            'event',
            'subjectType' => 'subject_type',
            'causerType'  => 'causer_type',
        ];

        $this->defaultSort = '-created_at';
    }

    public function index(Request $request): JsonResponse
    {
        $query = $this->getIndexQuery($request);
        $query->where('tenant_id', $request->user()->tenant_id);

        $result         = $this->getIndexPaginator($request);
        $result['data'] = ActivityLogDTO::collect($result['data']);

        return response()->json($result);
    }
}
