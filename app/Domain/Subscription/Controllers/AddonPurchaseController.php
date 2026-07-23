<?php

namespace App\Domain\Subscription\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Subscription\Actions\PurchaseAddonAction;
use App\Domain\Subscription\Models\AddonPurchase;
use App\Domain\Subscription\Requests\PurchaseAddonRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AddonPurchaseController extends Controller
{
    public function index(Request $request)
    {
        // TODO: Add filtering, pagination, etc.
        /** @var User $user */
        $user = Auth::user();

        return AddonPurchase::query()->forUser($user)->paginate();
    }

    public function store(PurchaseAddonRequest $request, PurchaseAddonAction $purchaseAction)
    {
        $addonId = $purchaseAction($request->validated());

        return response()->json(['id' => $addonId], Response::HTTP_CREATED);
    }

    public function show(string $id)
    {
        /** @var User $user */
        $user = Auth::user();

        $addon = AddonPurchase::query()->forUser($user)->findOrFail($id);

        return response()->json($addon);
    }
}
