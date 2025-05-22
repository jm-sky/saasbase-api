<?php

namespace App\Domain\Tenant\Controllers;

use App\Domain\Common\Models\Address;
use App\Domain\Common\Policies\AddressPolicy;
use App\Domain\Common\Resources\AddressResource;
use App\Domain\Common\Traits\HasActivityLogging;
use App\Domain\Tenant\Enums\TenantActivityType;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Models\TenantAddress;
use App\Domain\Tenant\Requests\StoreTenantAddressRequest;
use App\Domain\Tenant\Requests\UpdateTenantAddressRequest;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * @uses AddressPolicy
 */
class TenantAddressController extends Controller
{
    use AuthorizesRequests;
    use HasActivityLogging;

    public function index(Tenant $tenant): AnonymousResourceCollection
    {
        $this->authorize('viewAny', [Address::class, $tenant]);

        $addresses = $tenant->addresses()
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate()
        ;

        return AddressResource::collection($addresses);
    }

    public function store(StoreTenantAddressRequest $request, Tenant $tenant): JsonResponse
    {
        $this->authorize('create', [Address::class, $tenant]);
        $address = $tenant->addresses()->create($request->validated());
        $tenant->logModelActivity(TenantActivityType::AddressCreated->value, $address);

        return response()->json([
            'message' => 'Address created successfully.',
            'data'    => new AddressResource($address),
        ], Response::HTTP_CREATED);
    }

    public function show(Tenant $tenant, Address $address): AddressResource
    {
        $this->authorize('view', [$address, $tenant]);

        return new AddressResource($address);
    }

    public function update(UpdateTenantAddressRequest $request, Tenant $tenant, Address $address): JsonResponse
    {
        $this->authorize('update', [$address, $tenant]);
        $address->update($request->validated());
        $tenant->logModelActivity(TenantActivityType::AddressUpdated->value, $address);

        return response()->json([
            'message' => 'Address updated successfully.',
            'data'    => new AddressResource($address),
        ]);
    }

    public function destroy(Tenant $tenant, Address $address): JsonResponse
    {
        $this->authorize('delete', [$address, $tenant]);
        $tenant->logModelActivity(TenantActivityType::AddressDeleted->value, $address);
        $address->delete();

        return response()->json(['message' => 'Address deleted successfully.'], Response::HTTP_NO_CONTENT);
    }

    public function setDefault(Tenant $tenant, TenantAddress $address): JsonResponse
    {
        $tenant->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);
        $tenant->logModelActivity(TenantActivityType::AddressSetDefault->value, $address);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
