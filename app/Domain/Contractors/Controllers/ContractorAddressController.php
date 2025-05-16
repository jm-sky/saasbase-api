<?php

namespace App\Domain\Contractors\Controllers;

use App\Domain\Common\DTOs\AddressDTO;
use App\Domain\Common\Models\Address;
use App\Domain\Contractors\Enums\ContractorActivityType;
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

        activity()
            ->performedOn($contractor)
            ->withProperties([
                'contractor_id' => $contractor->id,
                'address_id'    => $address->id,
            ])
            ->event(ContractorActivityType::AddressCreated->value)
            ->log('Contractor address created')
        ;

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

        activity()
            ->performedOn($contractor)
            ->withProperties([
                'contractor_id' => $contractor->id,
                'address_id'    => $address->id,
            ])
            ->event(ContractorActivityType::AddressUpdated->value)
            ->log('Contractor address updated')
        ;

        return response()->json([
            'data' => AddressDTO::fromModel($address->fresh()),
        ]);
    }

    public function destroy(Contractor $contractor, ContractorAddress $address): JsonResponse
    {
        abort_if($address->addressable_id !== $contractor->id, Response::HTTP_NOT_FOUND);

        activity()
            ->performedOn($contractor)
            ->withProperties([
                'contractor_id' => $contractor->id,
                'address_id'    => $address->id,
            ])
            ->event(ContractorActivityType::AddressDeleted->value)
            ->log('Contractor address deleted')
        ;

        $address->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
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
