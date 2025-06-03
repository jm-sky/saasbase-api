<?php

namespace App\Domain\Tenant\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Auth\Models\User;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Domain\Subscription\Enums\FeatureName;

class TenantQuotaController extends Controller
{
    public function __invoke()
    {
        /** @var User $user */
        $user = Auth::user();

        $tenantId = $user->getTenantId();

        $tenant = Tenant::with('subscriptionPlan.features')->findOrFail($tenantId);

        $usedStorageBytes = Media::where('tenant_id', $tenantId)->sum('size');
        $usedStorageGB = round($usedStorageBytes / 1024 / 1024 / 1024, 2);

        $usedUsers = $tenant->users()->count();

        $features = $tenant->subscriptionPlan->features->pluck('pivot.value', 'name');

        $maxUsers = $this->parseLimit($features[FeatureName::MAX_USERS->value] ?? '0');
        $maxStorage = (float) ($features[FeatureName::STORAGE_GB->value] ?? 0);

        return response()->json([
            'usedStorageGb'      => $usedStorageGB,
            'availableStorageGb' => $maxStorage,
            'usedUsers'          => $usedUsers,
            'availableUsers'     => $maxUsers,
        ]);
    }

    private function parseLimit(string $value): int|string
    {
        return match (strtolower($value)) {
            'unlimited', '-1' => 'unlimited',
            default => (int) $value,
        };
    }
}
