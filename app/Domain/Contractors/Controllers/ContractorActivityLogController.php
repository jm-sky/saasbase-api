<?php

namespace App\Domain\Contractors\Controllers;

use App\Domain\Common\DTOs\ActivityLogDTO;
use App\Domain\Common\Filters\DateRangeFilter;
use App\Domain\Common\Models\Activity;
use App\Domain\Common\Traits\HasIndexQuery;
use App\Domain\Contractors\Models\Contractor;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;

class ContractorActivityLogController extends Controller
{
    use HasIndexQuery;

    protected int $defaultPerPage = 15;

    public function __construct()
    {
        $this->modelClass  = Activity::class;
        $this->defaultWith = ['causer', 'subject'];

        $this->filters = [
            AllowedFilter::exact('event'),
            AllowedFilter::exact('causerType', 'causer_type'),
            AllowedFilter::exact('causerId', 'causer_id'),
            AllowedFilter::custom('createdAt', new DateRangeFilter('created_at')),
        ];

        $this->sorts = [
            'createdAt' => 'created_at',
            'event',
            'causerType' => 'causer_type',
        ];

        $this->defaultSort = '-created_at';
    }

    public function index(Request $request, Contractor $contractor): JsonResponse
    {
        $query = $this->getIndexQuery($request);
        $query->where('subject_type', Contractor::class)
            ->where('subject_id', $contractor->id)
            ->where('tenant_id', $request->user()->getTenantId())
        ;

        $result         = $this->getIndexPaginator($request, query: $query);
        $result['data'] = ActivityLogDTO::collect($result['data']);

        return response()->json($result);
    }
}
