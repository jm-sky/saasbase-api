<?php

namespace App\Domain\Tenant\Controllers;

use App\Domain\Common\DTOs\AddressDTO;
use App\Domain\Common\Models\Address;
use App\Domain\Tenant\Enums\TenantActivityType;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Models\TenantAddress;
use App\Domain\Tenant\Requests\TenantAddressRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class TenantAddressController extends Controller
{
    public function index(Tenant $tenant): JsonResponse
    {
        $addresses = $tenant->addresses()->paginate();

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

    public function store(TenantAddressRequest $request, Tenant $tenant): JsonResponse
    {
        $address = $tenant->addresses()->create($request->validated());

        activity()
            ->performedOn($tenant)
            ->withProperties([
                'tenant_id'  => $tenant->id,
                'address_id' => $address->id,
            ])
            ->event(TenantActivityType::AddressCreated->value)
            ->log('Tenant address created')
        ;

        return response()->json([
            'data' => AddressDTO::fromModel($address),
        ], Response::HTTP_CREATED);
    }

    public function show(Tenant $tenant, TenantAddress $address): JsonResponse
    {
        abort_if($address->addressable_id !== $tenant->id, Response::HTTP_NOT_FOUND);

        return response()->json([
            'data' => AddressDTO::fromModel($address),
        ]);
    }

    public function update(TenantAddressRequest $request, Tenant $tenant, TenantAddress $address): JsonResponse
    {
        abort_if($address->addressable_id !== $tenant->id, Response::HTTP_NOT_FOUND);

        $address->update($request->validated());

        activity()
            ->performedOn($tenant)
            ->withProperties([
                'tenant_id'  => $tenant->id,
                'address_id' => $address->id,
            ])
            ->event(TenantActivityType::AddressUpdated->value)
            ->log('Tenant address updated')
        ;

        return response()->json([
            'data' => AddressDTO::fromModel($address->fresh()),
        ]);
    }

    public function destroy(Tenant $tenant, TenantAddress $address): JsonResponse
    {
        abort_if($address->addressable_id !== $tenant->id, Response::HTTP_NOT_FOUND);

        activity()
            ->performedOn($tenant)
            ->withProperties([
                'tenant_id'  => $tenant->id,
                'address_id' => $address->id,
            ])
            ->event(TenantActivityType::AddressDeleted->value)
            ->log('Tenant address deleted')
        ;

        $address->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function setDefault(Tenant $tenant, TenantAddress $address): JsonResponse
    {
        $tenant->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        activity()
            ->performedOn($tenant)
            ->withProperties([
                'tenant_id'  => $tenant->id,
                'address_id' => $address->id,
            ])
            ->event(TenantActivityType::AddressSetDefault->value)
            ->log('Tenant address set as default')
        ;

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
