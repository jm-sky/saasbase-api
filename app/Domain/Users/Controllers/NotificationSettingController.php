<?php

namespace App\Domain\Users\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Users\Models\NotificationSetting;
use App\Domain\Users\Requests\UpdateNotificationSettingRequest;
use App\Domain\Users\Requests\UpdateNotificationSettingsBulkRequest;
use App\Domain\Users\Resources\NotificationSettingResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class NotificationSettingController extends Controller
{
    public function index(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var Collection<int, NotificationSetting> $settings */
        $settings = $user->notificationSettings()->paginate();

        return response()->json(NotificationSettingResource::collection($settings));
    }

    public function update(UpdateNotificationSettingRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var NotificationSetting $setting */
        $setting = $user->notificationSettings()
            ->updateOrCreate(
                [
                    'channel'     => $request->channel,
                    'setting_key' => $request->settingKey,
                ],
                [
                    'enabled' => $request->enabled,
                ]
            )
        ;

        return response()->json(new NotificationSettingResource($setting));
    }

    public function updateBulk(UpdateNotificationSettingsBulkRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $settings = collect($request->settings)->map(function ($setting) use ($user) {
            return $user->notificationSettings()
                ->updateOrCreate(
                    [
                        'channel'     => $setting['channel'],
                        'setting_key' => $setting['settingKey'],
                    ],
                    [
                        'enabled' => $setting['enabled'],
                    ]
                )
            ;
        });

        return response()->json(NotificationSettingResource::collection($settings));
    }
}
