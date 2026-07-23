<?php

namespace App\Domain\Subscription\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Subscription\Models\SubscriptionInvoice;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class SubscriptionInvoiceController extends Controller
{
    public function index(Request $request)
    {
        // TODO: Add filtering, pagination, etc.
        /** @var User $user */
        $user = Auth::user();

        return SubscriptionInvoice::query()->forUser($user)->paginate();
    }

    public function show(string $id)
    {
        /** @var User $user */
        $user = Auth::user();

        $invoice = SubscriptionInvoice::query()->forUser($user)->findOrFail($id);

        return response()->json($invoice);
    }
}
