<?php

namespace App\Domain\Common\Controllers;

use App\Domain\Common\DTOs\CountryDTO;
use App\Domain\Common\Models\Country;
use App\Domain\Common\Requests\CountryRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class CountryController extends Controller
{
    public function index(): JsonResponse
    {
        $countries = Country::paginate();
        return response()->json(
            CountryDTO::collect($countries)
        );
    }

    public function store(CountryRequest $request): JsonResponse
    {
        $dto = CountryDTO::from($request->validated());
        $country = Country::create((array) $dto);

        return response()->json(
            CountryDTO::from($country),
            Response::HTTP_CREATED
        );
    }

    public function show(Country $country): JsonResponse
    {
        return response()->json(
            CountryDTO::from($country)
        );
    }

    public function update(CountryRequest $request, Country $country): JsonResponse
    {
        $dto = CountryDTO::from($request->validated());
        $country->update((array) $dto);

        return response()->json(
            CountryDTO::from($country)
        );
    }

    public function destroy(Country $country): JsonResponse
    {
        $country->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
