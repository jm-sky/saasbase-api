<?php

namespace App\Domain\Common\Controllers;

use App\Domain\Common\DTOs\CountryDTO;
use App\Domain\Common\Models\Country;
use App\Domain\Common\Requests\SearchCountryRequest;
use App\Domain\Common\Concerns\HasIndexQuery;
use App\Domain\Common\Filters\DateRangeFilter;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\AllowedFilter;

class CountryController extends Controller
{
    use HasIndexQuery;

    protected int $defaultPerPage = 15;

    public function __construct()
    {
        $this->modelClass = Country::class;

        $this->filters = [
            AllowedFilter::partial('name'),
            AllowedFilter::exact('code'),
            AllowedFilter::exact('code3'),
            AllowedFilter::exact('numericCode', 'numeric_code'),
            AllowedFilter::exact('phoneCode', 'phone_code'),
            AllowedFilter::partial('capital'),
            AllowedFilter::exact('currency'),
            AllowedFilter::exact('currencyCode', 'currency_code'),
            AllowedFilter::exact('currencySymbol', 'currency_symbol'),
            AllowedFilter::exact('tld'),
            AllowedFilter::partial('native'),
            AllowedFilter::partial('region'),
            AllowedFilter::partial('subregion'),
            AllowedFilter::exact('emoji'),
            AllowedFilter::exact('emojiU'),
            AllowedFilter::custom('createdAt', new DateRangeFilter('created_at')),
            AllowedFilter::custom('updatedAt', new DateRangeFilter('updated_at')),
        ];

        $this->sorts = [
            'name',
            'code',
            'code3',
            'numericCode' => 'numeric_code',
            'phoneCode' => 'phone_code',
            'capital',
            'currency',
            'currencyCode' => 'currency_code',
            'region',
            'subregion',
            'createdAt' => 'created_at',
            'updatedAt' => 'updated_at',
        ];

        $this->defaultSort = 'name';
    }

    public function index(SearchCountryRequest $request): JsonResponse
    {
        $result = $this->getIndexPaginator($request);
        $result['data'] = CountryDTO::collect($result['data']);

        return response()->json($result);
    }

    public function show(Country $country): JsonResponse
    {
        return response()->json(
            CountryDTO::from($country)
        );
    }
}
