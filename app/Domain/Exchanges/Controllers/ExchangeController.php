<?php

namespace App\Domain\Exchanges\Controllers;

use App\Domain\Common\Traits\HasIndexQuery;
use App\Domain\Exchanges\Models\Exchange;
use App\Domain\Exchanges\Requests\SearchExchangeRateRequest;
use App\Domain\Exchanges\Requests\SearchExchangeRequest;
use App\Domain\Exchanges\Resources\ExchangeRateResource;
use App\Domain\Exchanges\Resources\ExchangeResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\AllowedFilter;

class ExchangeController extends Controller
{
    use HasIndexQuery;

    protected int $defaultPerPage = 15;

    public function __construct()
    {
        $this->modelClass = Exchange::class;

        $this->filters = [
            AllowedFilter::exact('currency'),
        ];

        $this->sorts = [
            'name',
            'currency',
            'createdAt' => 'created_at',
            'updatedAt' => 'updated_at',
        ];

        $this->defaultSort = 'name';
    }

    public function index(SearchExchangeRequest $request): AnonymousResourceCollection
    {
        $exchanges = $this->getIndexPaginator($request);

        return ExchangeResource::collection($exchanges['data'])
            ->additional(['meta' => $exchanges['meta']])
        ;
    }

    public function show(Exchange $exchange): ExchangeResource
    {
        return new ExchangeResource($exchange);
    }

    public function getRates(SearchExchangeRateRequest $request, Exchange $exchange): AnonymousResourceCollection
    {
        $query = $exchange->rates();

        if ($date = $request->input('date')) {
            $query->where('date', $date);
        }

        $rates = $query->orderBy('date', 'desc')->get();

        return ExchangeRateResource::collection($rates);
    }
}
