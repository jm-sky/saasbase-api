<?php

namespace App\Domain\Contractors\Controllers;

use App\Domain\Contractors\Models\Contractor;
use App\Domain\Contractors\Models\ContractorAddress;
use App\Domain\Contractors\Requests\ContractorAddressRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ContractorAddressController extends Controller
{
    public function index(Contractor $contractor): JsonResponse
    {
        $addresses = $contractor->addresses()->paginate();

        return response()->json([
            'data' => $addresses,
        ]);
    }

    public function store(ContractorAddressRequest $request, Contractor $contractor): JsonResponse
    {
        $address = $contractor->addresses()->create($request->validated());

        return response()->json([
            'data' => $address,
        ], Response::HTTP_CREATED);
    }

    public function show(Contractor $contractor, ContractorAddress $address): JsonResponse
    {
        abort_if($address->addressable_id !== $contractor->id, Response::HTTP_NOT_FOUND);

        return response()->json([
            'data' => $address,
        ]);
    }

    public function update(ContractorAddressRequest $request, Contractor $contractor, ContractorAddress $address): JsonResponse
    {
        abort_if($address->addressable_id !== $contractor->id, Response::HTTP_NOT_FOUND);

        $address->update($request->validated());

        return response()->json([
            'data' => $address,
        ]);
    }

    public function destroy(Contractor $contractor, ContractorAddress $address): JsonResponse
    {
        abort_if($address->addressable_id !== $contractor->id, Response::HTTP_NOT_FOUND);

        $address->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
