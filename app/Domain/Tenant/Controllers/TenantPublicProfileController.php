<?php

namespace App\Domain\Tenant\Controllers;

use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Models\TenantPublicProfile;
use App\Domain\Tenant\Requests\TenantPublicProfileRequest;
use App\Domain\Tenant\Resources\TenantPublicProfileResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TenantPublicProfileController extends Controller
{
    public function show(Tenant $tenant): TenantPublicProfileResource
    {
        $profile = $tenant->publicProfile;

        if (!$profile) {
            throw new NotFoundHttpException('Public profile not found');
        }

        return new TenantPublicProfileResource($profile);
    }

    public function update(TenantPublicProfileRequest $request, Tenant $tenant): TenantPublicProfileResource
    {
        $profile = $tenant->publicProfile ?? new TenantPublicProfile(['tenant_id' => $tenant->id]);
        $profile->fill($request->validated());
        $profile->save();

        if ($request->hasFile('public_logo')) {
            $profile->addMedia($request->file('public_logo'))->toMediaCollection('public_logo');
        }

        if ($request->hasFile('banner_image')) {
            $profile->addMedia($request->file('banner_image'))->toMediaCollection('banner_image');
        }

        return new TenantPublicProfileResource($profile);
    }

    public function deleteMedia(Tenant $tenant, string $collection): JsonResponse
    {
        $profile = $tenant->publicProfile;

        if (!$profile) {
            throw new NotFoundHttpException('Public profile not found');
        }

        $profile->clearMediaCollection($collection);

        return response()->json(['message' => 'Media deleted successfully']);
    }
}
