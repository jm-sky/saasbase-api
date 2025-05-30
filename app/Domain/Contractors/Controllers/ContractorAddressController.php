<?php

namespace App\Domain\Contractors\Controllers;

use App\Domain\Common\Resources\AddressResource;
use App\Domain\Common\Traits\HasActivityLogging;
use App\Domain\Contractors\Enums\ContractorActivityType;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Contractors\Requests\StoreContractorAddressRequest;
use App\Domain\Contractors\Requests\UpdateContractorAddressRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ContractorAddressController extends Controller
{
    use HasActivityLogging;

    /**
     * Display a listing of the resource.
     */
    public function index(Contractor $contractor): AnonymousResourceCollection
    {
        $addresses = $contractor->addresses()->orderBy('is_default', 'desc')->get();

        return AddressResource::collection($addresses);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContractorAddressRequest $request, Contractor $contractor): AddressResource
    {
        $address = $contractor->addresses()->create($request->validated());

        $contractor->logModelActivity(ContractorActivityType::AddressCreated->value, $address);

        return new AddressResource($address);
    }

    /**
     * Display the specified resource.
     */
    public function show(Contractor $contractor, string $addressId): AddressResource
    {
        $address = $contractor->addresses()->findOrFail($addressId);

        return new AddressResource($address);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateContractorAddressRequest $request, Contractor $contractor, string $addressId): AddressResource
    {
        $address = $contractor->addresses()->findOrFail($addressId);
        $address->update($request->validated());

        $contractor->logModelActivity(ContractorActivityType::AddressUpdated->value, $address);

        return new AddressResource($address);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contractor $contractor, string $addressId): Response
    {
        $address = $contractor->addresses()->findOrFail($addressId);
        $address->delete();

        $contractor->logModelActivity(ContractorActivityType::AddressDeleted->value, $address);

        return response()->noContent();
    }

    public function setDefault(Contractor $contractor, string $addressId): JsonResponse
    {
        $contractorAddress = $contractor->addresses()->findOrFail($addressId);

        $contractor->addresses()->update(['is_default' => false]);
        $contractorAddress->update(['is_default' => true]);

        $contractor->logModelActivity(ContractorActivityType::AddressSetDefault->value, $contractorAddress);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
