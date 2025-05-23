<?php

namespace App\Domain\Users\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Resources\UserPreviewResource;
use App\Domain\Common\Resources\UserProfileLegacyResource;
use App\Domain\Tenant\Models\Tenant;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class PublicUserController extends Controller
{
    protected int $defaultPerPage = 15;

    protected ?Tenant $tenant;

    public function __construct()
    {
        /** @var User $user */
        $user = Auth::user();

        $this->tenant = $user->tenants()->firstWhere('tenants.id', $user->getTenantId());
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $users = $this->tenant->users()->get();

        return UserPreviewResource::collection($users);
    }

    public function show(User $user): UserProfileLegacyResource
    {
        return new UserProfileLegacyResource($user);
    }
}
