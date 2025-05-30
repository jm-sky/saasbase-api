<?php

namespace App\Domain\Subscription\Controllers;

use App\Domain\Subscription\Models\SubscriptionInvoice;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        // TODO: Add filtering, pagination, etc.
        return SubscriptionInvoice::paginate();
    }

    public function show(string $id)
    {
        $invoice = SubscriptionInvoice::findOrFail($id);

        return response()->json($invoice);
    }
}
