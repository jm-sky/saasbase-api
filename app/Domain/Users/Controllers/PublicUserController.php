<?php

namespace App\Domain\Users\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Users\DTOs\UserPreviewDTO;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

    public function index(Request $request): JsonResponse
    {
        $users          = $this->tenant->users()->get();
        $result         = [];
        $result['data'] = array_map(fn (array $user) => UserPreviewDTO::fromArray($user), $users->toArray());

        return response()->json($result);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json(['data' => UserPreviewDTO::from($user)]);
    }
}
