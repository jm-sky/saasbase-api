<?php

namespace App\Domain\Subscription\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Subscription\Actions\CancelSubscriptionAction;
use App\Domain\Subscription\Actions\CreateSubscriptionAction;
use App\Domain\Subscription\Actions\UpdateSubscriptionAction;
use App\Domain\Subscription\Models\Subscription;
use App\Domain\Subscription\Requests\CancelSubscriptionRequest;
use App\Domain\Subscription\Requests\StoreSubscriptionRequest;
use App\Domain\Subscription\Requests\UpdateSubscriptionRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        // TODO: Add filtering, pagination, etc.
        /** @var User $user */
        $user = Auth::user();

        return Subscription::query()->forUser($user)->paginate();
    }

    public function store(StoreSubscriptionRequest $request, CreateSubscriptionAction $createAction)
    {
        $subscriptionId = $createAction($request->toDto());

        return response()->json(['id' => $subscriptionId], Response::HTTP_CREATED);
    }

    public function show(string $id)
    {
        /** @var User $user */
        $user = Auth::user();

        $subscription = Subscription::query()->forUser($user)->findOrFail($id);

        return response()->json($subscription);
    }

    public function update(UpdateSubscriptionRequest $request, string $id, UpdateSubscriptionAction $updateAction)
    {
        /** @var User $user */
        $user = Auth::user();

        $subscription = Subscription::query()->forUser($user)->findOrFail($id);
        $updateAction($subscription->stripe_subscription_id, $request->validated());

        return response()->noContent();
    }

    public function destroy(CancelSubscriptionRequest $request, string $id, CancelSubscriptionAction $cancelAction)
    {
        /** @var User $user */
        $user = Auth::user();

        $subscription = Subscription::query()->forUser($user)->findOrFail($id);
        $cancelAction($subscription->stripe_subscription_id, $request->input('at_period_end', true));

        return response()->noContent();
    }
}
