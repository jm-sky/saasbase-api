<?php

namespace App\Domain\Expense\Controllers;

use App\Domain\Expense\DTOs\DimensionDataDTO;
use App\Domain\Expense\Enums\AllocationDimensionType;
use App\Domain\Expense\Requests\UpdateDimensionConfigurationRequest;
use App\Domain\Expense\Resources\DimensionConfigurationResource;
use App\Domain\Expense\Resources\DimensionDataResource;
use App\Domain\Expense\Resources\DimensionTypeResource;
use App\Domain\Expense\Services\DimensionVisibilityService;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class DimensionConfigurationController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private DimensionVisibilityService $dimensionService
    ) {
    }

    /**
     * Get current dimension configuration for tenant.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Domain\Auth\Models\User $user */
        $user     = Auth::user();
        $tenantId = $user->getTenantId();

        $dimensions        = $this->dimensionService->getAllDimensionsForTenant($tenantId);
        $enabledDimensions = $this->dimensionService->getEnabledDimensionsForTenant($tenantId);

        return response()->json([
            'data' => [
                'allDimensions'     => DimensionConfigurationResource::collection($dimensions),
                'enabledDimensions' => DimensionTypeResource::collection($enabledDimensions),
            ],
        ]);
    }

    /**
     * Update dimension configuration for tenant.
     */
    public function update(UpdateDimensionConfigurationRequest $request): JsonResponse
    {
        /** @var \App\Domain\Auth\Models\User $user */
        $user     = Auth::user();
        $tenantId = $user->getTenantId();

        $validated = $request->validated();

        try {
            $this->dimensionService->bulkUpdateDimensionConfigurations(
                $tenantId,
                $validated['configurations']
            );

            // Return updated configuration
            $dimensions = $this->dimensionService->getAllDimensionsForTenant($tenantId);

            return response()->json([
                'message' => 'Dimension configuration updated successfully',
                'data'    => [
                    'dimensions' => DimensionConfigurationResource::collection($dimensions),
                ],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => 'Configuration update failed',
                'error'   => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Reset dimension configuration to defaults.
     */
    public function resetToDefaults(): JsonResponse
    {
        /** @var \App\Domain\Auth\Models\User $user */
        $user     = Auth::user();
        $tenantId = $user->getTenantId();

        $this->dimensionService->resetToDefaults($tenantId);

        $dimensions = $this->dimensionService->getAllDimensionsForTenant($tenantId);

        return response()->json([
            'message' => 'Dimension configuration reset to defaults',
            'data'    => [
                'dimensions' => DimensionConfigurationResource::collection($dimensions),
            ],
        ]);
    }

    /**
     * Get available dimensions for selection (with their data).
     */
    public function availableDimensions(Request $request): JsonResponse
    {
        /** @var \App\Domain\Auth\Models\User $user */
        $user          = Auth::user();
        $tenantId      = $user->getTenantId();
        $dimensionType = $request->query('type');

        if ($dimensionType) {
            try {
                $dimension = AllocationDimensionType::from($dimensionType);
                $data      = $this->getDimensionData($dimension, $tenantId);

                $dimensionDataDTO = DimensionDataDTO::fromDimensionTypeWithItems($dimension, $data->toArray());

                return response()->json([
                    'data' => new DimensionDataResource($dimensionDataDTO),
                ]);
            } catch (\ValueError) {
                return response()->json([
                    'message' => 'Invalid dimension type',
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        // Return all available dimensions with their data
        $enabledDimensions = $this->dimensionService->getEnabledDimensionsForTenant($tenantId);
        $dimensionsData    = [];

        foreach ($enabledDimensions as $dimension) {
            $items            = $this->getDimensionData($dimension, $tenantId);
            $dimensionsData[] = DimensionDataDTO::fromDimensionTypeWithItems($dimension, $items->toArray());
        }

        return response()->json([
            'data' => [
                'dimensions' => DimensionDataResource::collection($dimensionsData),
            ],
        ]);
    }

    /**
     * Get dimension data for a specific dimension type.
     */
    private function getDimensionData(AllocationDimensionType $dimension, string $tenantId): \Illuminate\Database\Eloquent\Collection
    {
        $modelClass = $dimension->getMorphClass();

        if (!class_exists($modelClass)) {
            // @phpstan-ignore-next-line
            return collect();
        }

        // Handle special cases for User and Project models
        if (AllocationDimensionType::EMPLOYEES === $dimension) {
            // User model: get users in current tenant (via JWT token context)
            return $modelClass::query()->get(); // User scope handles tenancy automatically via JWT
        }

        if (AllocationDimensionType::PROJECT === $dimension) {
            // Project model: tenant-specific only (uses BelongsToTenant)
            return $modelClass::query()->where('tenant_id', $tenantId)->get();
        }

        // Standard allocation models: all use IsGlobalOrBelongsToTenant trait
        return $modelClass::forTenant($tenantId)->active()->get();
    }
}
