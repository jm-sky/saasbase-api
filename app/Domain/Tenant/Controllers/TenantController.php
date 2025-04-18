<?php

namespace App\Domain\Tenant\Controllers;

use App\Domain\Tenant\DTOs\TenantDTO;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Requests\TenantRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class TenantController extends Controller
{
    public function index(): JsonResponse
    {
        $tenants = Tenant::query()
            ->orderBy('created_at')
            ->get()
        ;

        return response()->json(
            TenantDTO::collect($tenants)
        );
    }

    public function store(TenantRequest $request): JsonResponse
    {
        $tenant = Tenant::create($request->validated());

        return response()->json(
            TenantDTO::from($tenant),
            201
        );
    }

    public function show(Tenant $tenant): JsonResponse
    {
        return response()->json(
            TenantDTO::from($tenant)
        );
    }

    public function update(TenantRequest $request, Tenant $tenant): JsonResponse
    {
        $tenant->update($request->validated());

        return response()->json(
            TenantDTO::from($tenant)
        );
    }

    public function destroy(Tenant $tenant): JsonResponse
    {
        $tenant->delete();

        return response()->json(null, 204);
    }
}
