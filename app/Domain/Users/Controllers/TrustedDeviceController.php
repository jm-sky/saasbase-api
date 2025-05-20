<?php

namespace App\Domain\Users\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Users\Models\TrustedDevice;
use App\Domain\Users\Resources\TrustedDeviceResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class TrustedDeviceController extends Controller
{
    public function index(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $devices = $user->trustedDevices()->paginate();

        return response()->json(TrustedDeviceResource::collection($devices));
    }

    public function destroy(TrustedDevice $device): JsonResponse
    {
        $this->authorize('delete', $device);

        $device->delete();

        return response()->json(null, 204);
    }

    public function destroyAll(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $user->trustedDevices()->delete();

        return response()->json(null, 204);
    }
}
