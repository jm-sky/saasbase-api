<?php

namespace App\Domains\Tenant\Controllers;

use App\Domains\Tenant\DTOs\TenantDTO;
use App\Domains\Tenant\Models\Tenant;
use App\Domains\Tenant\Requests\TenantRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TenantController extends Controller
{
    public function index(): JsonResponse
    {
        $tenants = Tenant::all();
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
