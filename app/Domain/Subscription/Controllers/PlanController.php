<?php

namespace App\Domain\Subscription\Controllers;

use App\Domain\Subscription\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        // TODO: Add filtering, pagination, etc.
        return SubscriptionPlan::paginate();
    }

    public function show(string $id)
    {
        $plan = SubscriptionPlan::findOrFail($id);

        return response()->json($plan);
    }
}
