<?php

namespace App\Domain\Contractors\Controllers;

use App\Domain\Common\DTOs\AddressDTO;
use App\Domain\Common\Models\Address;
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
            'data' => collect($addresses->items())->map(fn (Address $address) => AddressDTO::fromModel($address)),
            'meta' => [
                'current_page' => $addresses->currentPage(),
                'last_page'    => $addresses->lastPage(),
                'per_page'     => $addresses->perPage(),
                'total'        => $addresses->total(),
            ],
        ]);
    }

    public function store(ContractorAddressRequest $request, Contractor $contractor): JsonResponse
    {
        $address = $contractor->addresses()->create($request->validated());

        return response()->json([
            'data' => AddressDTO::fromModel($address),
        ], Response::HTTP_CREATED);
    }

    public function show(Contractor $contractor, ContractorAddress $address): JsonResponse
    {
        abort_if($address->addressable_id !== $contractor->id, Response::HTTP_NOT_FOUND);

        return response()->json([
            'data' => AddressDTO::fromModel($address),
        ]);
    }

    public function update(ContractorAddressRequest $request, Contractor $contractor, ContractorAddress $address): JsonResponse
    {
        abort_if($address->addressable_id !== $contractor->id, Response::HTTP_NOT_FOUND);

        $address->update($request->validated());

        return response()->json([
            'data' => AddressDTO::fromModel($address->fresh()),
        ]);
    }

    public function destroy(Contractor $contractor, ContractorAddress $address): JsonResponse
    {
        abort_if($address->addressable_id !== $contractor->id, Response::HTTP_NOT_FOUND);

        $address->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
