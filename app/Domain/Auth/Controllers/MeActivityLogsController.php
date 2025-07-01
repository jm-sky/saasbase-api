<?php

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Common\DTOs\ActivityLogDTO;
use App\Domain\Common\Filters\DateRangeFilter;
use App\Domain\Common\Models\Activity;
use App\Domain\Common\Traits\HasIndexQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;

class MeActivityLogsController extends Controller
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

    public function __invoke(Request $request): JsonResponse
    {
        $query = $this->getIndexQuery($request);
        $query->where('causer_type', User::class)
            ->where('causer_id', $request->user()->id)
            ->where(function ($query) use ($request) {
                $query->where('tenant_id', $request->user()->getTenantId())
                    ->orWhere('tenant_id', null)
                ;
            })
        ;

        $result         = $this->getIndexPaginator($request, query: $query);
        $result['data'] = ActivityLogDTO::collect($result['data']);

        return response()->json($result);
    }
}
