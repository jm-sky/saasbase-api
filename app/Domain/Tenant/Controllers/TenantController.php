<?php

namespace App\Domain\Tenant\Controllers;

use App\Domain\Tenant\DTOs\TenantDTO;
use App\Domain\Tenant\DTOs\TenantSimpleDTO;
use App\Domain\Tenant\Enums\TenantActivityType;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Requests\TenantRequest;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): JsonResponse
    {
        $tenants = $request->user()->tenants()->orderBy('created_at')->get();

        return response()->json([
            'data' => TenantSimpleDTO::collect($tenants),
        ]);
    }

    public function store(TenantRequest $request): JsonResponse
    {
        // TODO: Create enum for tenant roles
        $tenant = Tenant::create($request->validated());
        $request->user()->tenants()->attach($tenant, ['role' => 'admin']);

        activity()
            ->performedOn($tenant)
            ->withProperties([
                'tenant_id' => $tenant->id,
            ])
            ->event(TenantActivityType::Created->value)
            ->log('Tenant created')
        ;

        return response()->json([
            'message' => 'Tenant created successfully.',
            'data'    => TenantDTO::from($tenant),
        ], Response::HTTP_CREATED);
    }

    public function show(Request $request, Tenant $tenant): JsonResponse
    {
        $this->authorize('view', $tenant);

        return response()->json([
            'data' => TenantDTO::from($tenant),
        ]);
    }

    public function update(TenantRequest $request, Tenant $tenant): JsonResponse
    {
        $this->authorize('update', $tenant);
        $tenant->update($request->validated());

        activity()
            ->performedOn($tenant)
            ->withProperties([
                'tenant_id' => $tenant->id,
            ])
            ->event(TenantActivityType::Updated->value)
            ->log('Tenant updated')
        ;

        return response()->json([
            'message' => 'Tenant updated successfully.',
            'data'    => TenantDTO::from($tenant),
        ]);
    }

    public function destroy(Request $request, Tenant $tenant): JsonResponse
    {
        $this->authorize('delete', $tenant);

        activity()
            ->performedOn($tenant)
            ->withProperties([
                'tenant_id' => $tenant->id,
            ])
            ->event(TenantActivityType::Deleted->value)
            ->log('Tenant deleted')
        ;

        $tenant->delete();

        return response()->json(['message' => 'Tenant deleted successfully.'], Response::HTTP_NO_CONTENT);
    }
}
