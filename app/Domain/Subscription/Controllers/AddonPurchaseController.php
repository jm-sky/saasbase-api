<?php

namespace App\Domain\Subscription\Controllers;

use App\Domain\Subscription\Actions\PurchaseAddonAction;
use App\Domain\Subscription\Models\AddonPurchase;
use App\Domain\Subscription\Requests\PurchaseAddonRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

class AddonPurchaseController extends Controller
{
    public function index(Request $request)
    {
        // TODO: Add filtering, pagination, etc.
        return AddonPurchase::paginate();
    }

    public function store(PurchaseAddonRequest $request, PurchaseAddonAction $purchaseAction)
    {
        $addonId = $purchaseAction($request->validated());

        return response()->json(['id' => $addonId], Response::HTTP_CREATED);
    }

    public function show(string $id)
    {
        $addon = AddonPurchase::findOrFail($id);

        return response()->json($addon);
    }
}
