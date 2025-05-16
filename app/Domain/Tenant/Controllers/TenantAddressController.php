<?php

namespace App\Domain\Tenant\Controllers;

use App\Domain\Common\DTOs\AddressDTO;
use App\Domain\Common\Models\Address;
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

        return response()->json([
            'data' => AddressDTO::fromModel($address->fresh()),
        ]);
    }

    public function destroy(Tenant $tenant, TenantAddress $address): JsonResponse
    {
        abort_if($address->addressable_id !== $tenant->id, Response::HTTP_NOT_FOUND);

        $address->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function setDefault(Tenant $tenant, TenantAddress $address): JsonResponse
    {
        $tenant->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
