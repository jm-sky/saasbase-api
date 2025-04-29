<?php

namespace App\Domain\Tenant\Controllers;

use App\Domain\Tenant\DTOs\TenantDTO;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Requests\TenantRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenants = $request->user()->tenants()->orderBy('created_at')->get();

        return response()->json(
            TenantDTO::collect($tenants)
        );
    }

    public function store(TenantRequest $request): JsonResponse
    {
        $tenant = Tenant::create($request->validated());
        $request->user()->tenants()->attach($tenant);

        return response()->json(
            TenantDTO::from($tenant),
            Response::HTTP_CREATED,
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
        $tenant = $request->user()->tenants()->where('id', $tenant->id)->firstOrFail();
        $tenant->update($request->validated());

        return response()->json(
            TenantDTO::from($tenant)
        );
    }

    public function destroy(Request $request, Tenant $tenant): JsonResponse
    {
        $tenant = $request->user()->tenants()->where('id', $tenant->id)->firstOrFail();
        $tenant->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
