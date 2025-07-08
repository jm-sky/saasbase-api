<?php

namespace App\Domain\Financial\Controllers;

use App\Domain\Common\Filters\AdvancedFilter;
use App\Domain\Common\Filters\ComboSearchFilter;
use App\Domain\Common\Traits\HasIndexQuery;
use App\Domain\Financial\Models\VatRate;
use App\Domain\Products\Resources\VatRateResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Spatie\QueryBuilder\AllowedFilter;

class VatRateController extends Controller
{
    use HasIndexQuery;

    protected int $defaultPerPage = 15;

    public function __construct()
    {
        $this->modelClass = VatRate::class;
        $this->filters    = [
            AllowedFilter::custom('search', new ComboSearchFilter(['name'])),
            AllowedFilter::custom('name', new AdvancedFilter()),
            AllowedFilter::custom('rate', new AdvancedFilter()),
        ];
        $this->sorts = [
            'name',
            'rate',
            'createdAt' => 'created_at',
            'updatedAt' => 'updated_at',
        ];
        $this->defaultSort = 'name';
    }

    /**
     * Display a listing of the VAT rates.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = $this->getIndexQuery($request);

        $query = $query->where('active', true);
        $query = $query->where('valid_from', '<=', now());

        $rates = $this->getIndexPaginator($request, query: $query);

        return VatRateResource::collection($rates['data'])
            ->additional(['meta' => $rates['meta']])
        ;
    }

    /**
     * Store a newly created VAT rate.
     */
    public function store(Request $request): VatRateResource
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);
        $vatRate = VatRate::create($validated);

        return new VatRateResource($vatRate);
    }

    /**
     * Remove the specified VAT rate.
     */
    public function destroy(VatRate $vatRate): \Illuminate\Http\JsonResponse
    {
        $vatRate->delete();

        return response()->json(['message' => 'Vat rate deleted successfully.'], Response::HTTP_NO_CONTENT);
    }
}
