<?php

namespace App\Domain\Common\Controllers;

use App\Domain\Common\Filters\AdvancedFilter;
use App\Domain\Common\Filters\ComboSearchFilter;
use App\Domain\Common\Models\MeasurementUnit;
use App\Domain\Common\Traits\HasIndexQuery;
use App\Domain\Products\Resources\MeasurementUnitResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Spatie\QueryBuilder\AllowedFilter;

class MeasurementUnitController extends Controller
{
    use HasIndexQuery;

    protected int $defaultPerPage = 15;

    public function __construct()
    {
        $this->modelClass = MeasurementUnit::class;
        $this->filters    = [
            AllowedFilter::custom('search', new ComboSearchFilter(['name', 'code'])),
            AllowedFilter::custom('name', new AdvancedFilter()),
            AllowedFilter::custom('code', new AdvancedFilter()),
        ];
        $this->sorts = [
            'name',
            'code',
            'createdAt' => 'created_at',
            'updatedAt' => 'updated_at',
        ];
        $this->defaultSort = 'name';
    }

    /**
     * Display a listing of the measurement units.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $units = $this->getIndexPaginator($request);

        return MeasurementUnitResource::collection($units['data'])
            ->additional(['meta' => $units['meta']])
        ;
    }

    /**
     * Store a newly created measurement unit.
     */
    public function store(Request $request): MeasurementUnitResource
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:32'],
        ]);
        $unit = MeasurementUnit::create($validated);

        return new MeasurementUnitResource($unit);
    }

    /**
     * Remove the specified measurement unit.
     */
    public function destroy(MeasurementUnit $measurementUnit): \Illuminate\Http\JsonResponse
    {
        $measurementUnit->delete();

        return response()->json(['message' => 'Measurement unit deleted successfully.'], Response::HTTP_NO_CONTENT);
    }
}
