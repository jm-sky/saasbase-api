<?php

namespace App\Domain\Users\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Users\Models\UserTableSetting;
use App\Domain\Users\Requests\StoreTableSettingRequest;
use App\Domain\Users\Requests\UpdateTableSettingRequest;
use App\Domain\Users\Resources\UserTableSettingResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserTableSettingController extends Controller
{
    public function index(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $settings = $user->tableSettings()->paginate();

        return response()->json(UserTableSettingResource::collection($settings));
    }

    public function store(StoreTableSettingRequest $request): JsonResponse
    {
        $setting = new UserTableSetting([
            'user_id' => Auth::id(),
        ]);
        $setting->fill($request->validated());
        $setting->save();

        return response()->json(new UserTableSettingResource($setting));
    }

    public function update(UpdateTableSettingRequest $request, UserTableSetting $setting): JsonResponse
    {
        $this->authorize('update', $setting);

        $setting->fill($request->validated());
        $setting->save();

        return response()->json(new UserTableSettingResource($setting));
    }

    public function destroy(UserTableSetting $setting): JsonResponse
    {
        $this->authorize('delete', $setting);

        $setting->delete();

        return response()->json(null, 204);
    }

    public function setDefault(UserTableSetting $setting): JsonResponse
    {
        $this->authorize('update', $setting);

        // Remove default flag from other settings for this entity
        /** @var User $user */
        $user = Auth::user();
        $user->tableSettings()
            ->where('entity', $setting->entity)
            ->where('is_default', true)
            ->update(['is_default' => false])
        ;

        $setting->is_default = true;
        $setting->save();

        return response()->json(new UserTableSettingResource($setting));
    }
}
