<?php

namespace App\Domain\Financial\Controllers;

use App\Domain\Financial\Models\PaymentMethod;
use App\Domain\Financial\Requests\StorePaymentMethodRequest;
use App\Domain\Financial\Requests\UpdatePaymentMethodRequest;
use App\Domain\Financial\Resources\PaymentMethodResource;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class PaymentMethodController extends Controller
{
    public function index()
    {
        return PaymentMethodResource::collection(PaymentMethod::paginate());
    }

    public function show(string $id)
    {
        $paymentMethod = PaymentMethod::findOrFail($id);

        return response()->json(['data' => new PaymentMethodResource($paymentMethod)]);
    }

    public function store(StorePaymentMethodRequest $request)
    {
        $paymentMethod = PaymentMethod::create($request->validated());

        return response()->json(['data' => new PaymentMethodResource($paymentMethod)], Response::HTTP_CREATED);
    }

    public function update(UpdatePaymentMethodRequest $request, $id)
    {
        $paymentMethod = PaymentMethod::findOrFail($id);
        $paymentMethod->update($request->validated());

        return response()->json(['data' => new PaymentMethodResource($paymentMethod)]);
    }

    public function destroy($id)
    {
        $paymentMethod = PaymentMethod::findOrFail($id);
        $paymentMethod->delete();

        return response()->noContent();
    }
}
