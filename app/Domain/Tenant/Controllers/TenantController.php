<?php

namespace App\Domain\Tenant\Controllers;

use App\Domain\Common\Traits\HasActivityLogging;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Requests\StoreTenantRequest;
use App\Domain\Tenant\Requests\UpdateTenantRequest;
use App\Domain\Tenant\Resources\TenantPreviewResource;
use App\Domain\Tenant\Resources\TenantResource;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class TenantController extends Controller
{
    use AuthorizesRequests;
    use HasActivityLogging;

    public function index(Request $request): AnonymousResourceCollection
    {
        $tenants = $request->user()->tenants()->orderBy('created_at')->get();

        return TenantResource::collection($tenants);
    }

    public function indexPreview(Request $request): AnonymousResourceCollection
    {
        $tenants = $request->user()->tenants()->orderBy('created_at')->get();

        return TenantPreviewResource::collection($tenants);
    }

    public function store(StoreTenantRequest $request): TenantResource
    {
        $tenantData = $request->validated('tenant');
        $tenant     = Tenant::create($tenantData);

        if ($request->has('bankAccount') && $request->validated('bankAccount') && $request->validated('bankAccount')['iban']) {
            $tenant->bankAccounts()->create($request->validated('bankAccount'));
        }

        if ($request->has('address') && $request->validated('address') && $request->validated('address')['street']) {
            $tenant->addresses()->create($request->validated('address'));
        }

        $request->user()->tenants()->attach($tenant, ['role' => 'admin']);

        return new TenantResource($tenant);
    }

    public function show(Request $request, Tenant $tenant): TenantResource
    {
        $this->authorize('view', $tenant);

        return new TenantResource($tenant);
    }

    public function update(UpdateTenantRequest $request, Tenant $tenant): JsonResponse
    {
        $this->authorize('update', $tenant);
        $tenant->update($request->validated());

        return response()->json([
            'message' => 'Tenant updated successfully.',
            'data'    => new TenantResource($tenant),
        ]);
    }

    public function destroy(Request $request, Tenant $tenant): JsonResponse
    {
        $this->authorize('delete', $tenant);
        $tenant->delete();

        return response()->json(['message' => 'Tenant deleted successfully.'], Response::HTTP_NO_CONTENT);
    }
}
