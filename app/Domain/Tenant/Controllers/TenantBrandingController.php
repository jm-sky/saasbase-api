<?php

namespace App\Domain\Tenant\Controllers;

use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Models\TenantBranding;
use App\Domain\Tenant\Requests\TenantBrandingRequest;
use App\Domain\Tenant\Resources\TenantBrandingResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class TenantBrandingController extends Controller
{
    public function show(Tenant $tenant): TenantBrandingResource
    {
        $branding = $tenant->branding;

        if (!$branding) {
            $branding = $tenant->branding()->create();
        }

        return new TenantBrandingResource($branding);
    }

    public function update(TenantBrandingRequest $request, Tenant $tenant): TenantBrandingResource
    {
        /** @var TenantBranding $branding */
        $branding = $tenant->branding ?? new TenantBranding(['tenant_id' => $tenant->id]);
        $branding->fill($request->validated());
        $branding->save();

        if ($request->hasFile('logo')) {
            $branding->addMedia($request->file('logo'))->toMediaCollection('logo');
        }

        if ($request->hasFile('favicon')) {
            $branding->addMedia($request->file('favicon'))->toMediaCollection('favicon');
        }

        if ($request->hasFile('custom_font')) {
            $branding->addMedia($request->file('custom_font'))->toMediaCollection('custom_font');
        }

        if ($request->hasFile('pdf_logo')) {
            $branding->addMedia($request->file('pdf_logo'))->toMediaCollection('pdf_logo');
        }

        if ($request->hasFile('email_header_image')) {
            $branding->addMedia($request->file('email_header_image'))->toMediaCollection('email_header_image');
        }

        return new TenantBrandingResource($branding);
    }

    public function deleteMedia(Tenant $tenant, string $collection): JsonResponse
    {
        /** @var TenantBranding $branding */
        $branding = $tenant->branding;

        if (!$branding) {
            return response()->json(['message' => 'Branding not found'], Response::HTTP_NOT_FOUND);
        }

        $branding->clearMediaCollection($collection);

        return response()->json(['message' => 'Media deleted successfully']);
    }
}
