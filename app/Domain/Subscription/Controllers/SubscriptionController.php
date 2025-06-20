<?php

namespace App\Domain\Subscription\Controllers;

use App\Domain\Subscription\Actions\CancelSubscriptionAction;
use App\Domain\Subscription\Actions\CreateSubscriptionAction;
use App\Domain\Subscription\Actions\UpdateSubscriptionAction;
use App\Domain\Subscription\Models\Subscription;
use App\Domain\Subscription\Requests\CancelSubscriptionRequest;
use App\Domain\Subscription\Requests\StoreSubscriptionRequest;
use App\Domain\Subscription\Requests\UpdateSubscriptionRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        // TODO: Add filtering, pagination, etc.
        return Subscription::paginate();
    }

    public function store(StoreSubscriptionRequest $request, CreateSubscriptionAction $createAction)
    {
        $subscriptionId = $createAction($request->toDto());

        return response()->json(['id' => $subscriptionId], Response::HTTP_CREATED);
    }

    public function show(string $id)
    {
        $subscription = Subscription::findOrFail($id);

        return response()->json($subscription);
    }

    public function update(UpdateSubscriptionRequest $request, string $id, UpdateSubscriptionAction $updateAction)
    {
        $subscription = Subscription::findOrFail($id);
        $updateAction($subscription->stripe_subscription_id, $request->validated());

        return response()->noContent();
    }

    public function destroy(CancelSubscriptionRequest $request, string $id, CancelSubscriptionAction $cancelAction)
    {
        $subscription = Subscription::findOrFail($id);
        $cancelAction($subscription->stripe_subscription_id, $request->input('at_period_end', true));

        return response()->noContent();
    }
}
