<?php

namespace App\Domain\Exchanges\Controllers;

use App\Domain\Common\Traits\HasIndexQuery;
use App\Domain\Exchanges\Models\ExchangeRate;
use App\Domain\Exchanges\Requests\SearchExchangeRateRequest;
use App\Domain\Exchanges\Resources\ExchangeRateResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\AllowedFilter;

class ExchangeRateController extends Controller
{
    use HasIndexQuery;

    protected int $defaultPerPage = 15;

    public function __construct()
    {
        $this->modelClass = ExchangeRate::class;

        $this->filters = [
            AllowedFilter::exact('base_currency'),
            AllowedFilter::exact('currency'),
            AllowedFilter::exact('date'),
            AllowedFilter::exact('table'),
            AllowedFilter::exact('source'),
        ];

        $this->sorts = [
            'date',
            'rate',
            'createdAt' => 'created_at',
        ];

        $this->defaultSort = '-date';
    }

    public function index(SearchExchangeRateRequest $request): AnonymousResourceCollection
    {
        $rates = $this->getIndexPaginator($request);

        return ExchangeRateResource::collection($rates['data'])
            ->additional(['meta' => $rates['meta']])
        ;
    }

    public function show(ExchangeRate $exchangeRate): ExchangeRateResource
    {
        return new ExchangeRateResource($exchangeRate);
    }
}
