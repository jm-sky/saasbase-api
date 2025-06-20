<?php

namespace App\Domain\Exchanges\Controllers;

use App\Domain\Exchanges\Models\Currency;
use App\Domain\Exchanges\Resources\CurrencyResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CurrencyController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return CurrencyResource::collection(Currency::all());
    }
}
