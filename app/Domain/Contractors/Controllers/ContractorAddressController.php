<?php

namespace App\Domain\Contractors\Controllers;

use App\Domain\Common\Resources\AddressResource;
use App\Domain\Contractors\Enums\ContractorActivityType;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Contractors\Models\ContractorAddress;
use App\Domain\Contractors\Requests\StoreContractorAddressRequest;
use App\Domain\Contractors\Requests\UpdateContractorAddressRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ContractorAddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Contractor $contractor): AnonymousResourceCollection
    {
        return AddressResource::collection($contractor->addresses);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContractorAddressRequest $request, Contractor $contractor): AddressResource
    {
        $address = $contractor->addresses()->create($request->validated());

        activity()
            ->performedOn($contractor)
            ->withProperties([
                'contractor_id' => $contractor->id,
                'address_id'    => $address->id,
            ])
            ->event(ContractorActivityType::AddressCreated->value)
            ->log('Contractor address created')
        ;

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

        activity()
            ->performedOn($contractor)
            ->withProperties([
                'contractor_id' => $contractor->id,
                'address_id'    => $address->id,
            ])
            ->event(ContractorActivityType::AddressUpdated->value)
            ->log('Contractor address updated')
        ;

        return new AddressResource($address);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contractor $contractor, string $addressId): Response
    {
        $address = $contractor->addresses()->findOrFail($addressId);
        $address->delete();

        activity()
            ->performedOn($contractor)
            ->withProperties([
                'contractor_id' => $contractor->id,
                'address_id'    => $address->id,
            ])
            ->event(ContractorActivityType::AddressDeleted->value)
            ->log('Contractor address deleted')
        ;

        return response()->noContent();
    }

    public function setDefault(Contractor $contractor, ContractorAddress $address): JsonResponse
    {
        $contractor->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        activity()
            ->performedOn($contractor)
            ->withProperties([
                'contractor_id' => $contractor->id,
                'address_id'    => $address->id,
            ])
            ->event(ContractorActivityType::AddressSetDefault->value)
            ->log('Contractor address set as default')
        ;

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
