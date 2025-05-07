<?php

namespace App\Domain\Exchanges\Controllers;

use App\Domain\Exchanges\DTOs\ExchangeDTO;
use App\Domain\Exchanges\DTOs\ExchangeRateDTO;
use App\Domain\Exchanges\Models\Exchange;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExchangeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Exchange::query();

        if ($currency = $request->input('currency')) {
            $query->where('currency', $currency);
        }

        $exchanges = $query->get()
            ->map(fn (Exchange $exchange) => ExchangeDTO::fromModel($exchange))
        ;

        return response()->json($exchanges);
    }

    public function show(Exchange $exchange): JsonResponse
    {
        return response()->json(['data' => ExchangeDTO::fromModel($exchange)]);
    }

    public function getRates(Request $request, Exchange $exchange): JsonResponse
    {
        $query = $exchange->rates();

        if ($date = $request->input('date')) {
            $query->where('date', $date);
        }

        $rates = $query
            ->orderBy('date', 'desc')
            ->get()
            ->map(fn ($rate) => ExchangeRateDTO::fromModel($rate))
        ;

        return response()->json($rates);
    }
}
