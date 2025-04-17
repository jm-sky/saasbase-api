<?php

namespace App\Domain\Common\Controllers;

use App\Domain\Common\DTOs\CountryDTO;
use App\Domain\Common\Models\Country;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class CountryController extends Controller
{
    public function index(): JsonResponse
    {
        $countries = Country::paginate();
        return response()->json(
            CountryDTO::collect($countries->items())
        );
    }

    public function show(Country $country): JsonResponse
    {
        return response()->json(
            CountryDTO::from($country)
        );
    }
}
