<?php

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\Actions\GetUserSettingsAction;
use App\Domain\Auth\Actions\UpdateUserLanguageAction;
use App\Domain\Auth\Actions\UpdateUserSettingsAction;
use App\Domain\Auth\Requests\UpdateUserLanguageRequest;
use App\Domain\Auth\Requests\UpdateUserSettingsRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserSettingsController extends Controller
{
    public function show(GetUserSettingsAction $action): JsonResponse
    {
        $settings = $action->execute(Auth::user());

        return response()->json($settings);
    }

    public function update(
        UpdateUserSettingsRequest $request,
        UpdateUserSettingsAction $action
    ): JsonResponse {
        $settings = $action->execute(Auth::user(), $request->validated());

        return response()->json($settings);
    }

    public function updateLanguage(
        UpdateUserLanguageRequest $request,
        UpdateUserLanguageAction $action
    ): JsonResponse {
        $settings = $action->execute(Auth::user(), $request->validated('language'));

        return response()->json($settings);
    }
}
