<?php

namespace App\Domain\Auth\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MeController extends Controller
{
    public function __invoke(Request $request)
    {
        return response()->json($request->user());
    }
}
