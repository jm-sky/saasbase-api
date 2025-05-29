<?php

namespace App\Domain\Users\Controllers;

use App\Domain\Users\Models\UserPreference;
use App\Domain\Users\Requests\UpdatePreferenceRequest;
use App\Domain\Users\Resources\UserPreferenceResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserPreferenceController
{
    public function show(): JsonResponse
    {
        $preferences = Auth::user()->preferences;

        return response()->json(new UserPreferenceResource($preferences));
    }

    public function update(UpdatePreferenceRequest $request): JsonResponse
    {
        $preferences = Auth::user()->preferences ?? new UserPreference(['user_id' => Auth::id()]);
        $preferences->fill($request->validated());
        $preferences->save();

        return response()->json(new UserPreferenceResource($preferences));
    }

    public function reset(): JsonResponse
    {
        $preferences = Auth::user()->preferences ?? new UserPreference(['user_id' => Auth::id()]);
        $preferences->fill([
            'language'              => null,
            'timezone'              => null,
            'decimal_separator'     => null,
            'date_format'           => null,
            'dark_mode'             => null,
            'is_sound_enabled'      => null,
            'is_profile_public'     => false,
            'field_visibility'      => null,
            'visibility_per_tenant' => null,
        ]);
        $preferences->save();

        return response()->json(new UserPreferenceResource($preferences));
    }
}
