<?php

namespace App\Domain\Subscription\Controllers;

use App\Domain\Subscription\Models\SubscriptionPlan;
use App\Domain\Subscription\Resources\SubscriptionPlanResource;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SubscriptionPlanController extends Controller
{
    public function index(Request $request)
    {
        $query = SubscriptionPlan::query()
            ->with(['features.feature', 'prices'])
            ->where('is_active', true)
            ->when($request->has('billing_period'), function ($query) use ($request) {
                $query->whereHas('prices', function ($q) use ($request) {
                    $q->where('billing_period', $request->billing_period)
                        ->where('is_active', true)
                    ;
                });
            })
            ->when($request->has('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                    ;
                });
            })
        ;

        return SubscriptionPlanResource::collection(
            $query->paginate($request->input('perPage', 15))
        );
    }

    public function show(string $id)
    {
        $plan = SubscriptionPlan::with(['features.feature', 'prices'])
            ->findOrFail($id)
        ;

        return new SubscriptionPlanResource($plan);
    }
}
