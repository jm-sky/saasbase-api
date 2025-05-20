<?php

namespace App\Domain\Common\Controllers;

use App\Domain\Common\Filters\AdvancedFilter;
use App\Domain\Common\Filters\ComboSearchFilter;
use App\Domain\Common\Models\Country;
use App\Domain\Common\Requests\SearchCountryRequest;
use App\Domain\Common\Resources\CountryResource;
use App\Domain\Common\Traits\HasIndexQuery;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\AllowedFilter;

class CountryController extends Controller
{
    use HasIndexQuery;
    use AuthorizesRequests;

    protected int $defaultPerPage = 15;

    public function __construct()
    {
        $this->modelClass = Country::class;

        $this->filters = [
            AllowedFilter::custom('search', new ComboSearchFilter(['name', 'code', 'code3'])),
            AllowedFilter::custom('name', new AdvancedFilter()),
            AllowedFilter::custom('code', new AdvancedFilter()),
            AllowedFilter::custom('code3', new AdvancedFilter()),
            AllowedFilter::custom('numericCode', new AdvancedFilter(), 'numeric_code'),
            AllowedFilter::custom('phoneCode', new AdvancedFilter(), 'phone_code'),
            AllowedFilter::custom('region', new AdvancedFilter()),
            AllowedFilter::custom('subregion', new AdvancedFilter()),
            AllowedFilter::custom('currency', new AdvancedFilter()),
            AllowedFilter::custom('currencyCode', new AdvancedFilter(), 'currency_code'),
        ];

        $this->sorts = [
            'name',
            'code',
            'code3',
            'numericCode' => 'numeric_code',
            'phoneCode'   => 'phone_code',
            'region',
            'subregion',
        ];

        $this->defaultSort = 'name';
    }

    public function index(SearchCountryRequest $request): AnonymousResourceCollection
    {
        $countries = $this->getIndexPaginator($request);

        return CountryResource::collection($countries['data'])
            ->additional(['meta' => $countries['meta']])
        ;
    }

    public function show(Country $country): CountryResource
    {
        return new CountryResource($country);
    }
}
