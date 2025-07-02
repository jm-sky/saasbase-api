<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\Expense\Enums\AllocationDimensionType;
use App\Domain\Expense\Models\TenantDimensionConfiguration;
use App\Domain\Expense\Services\DimensionVisibilityService;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class DimensionVisibilityServiceTest extends TestCase
{
    use RefreshDatabase;

    private DimensionVisibilityService $service;

    private User $user;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(DimensionVisibilityService::class);

        // Create a tenant and user for testing
        $this->tenant = Tenant::factory()->create();
        $this->user   = User::factory()->create();

        // Associate user with tenant
        $this->user->tenants()->attach($this->tenant->id, ['role' => 'admin']);
    }

    public function testInitializeDefaultConfigurationForTenant(): void
    {
        // Act
        $this->service->initializeDefaultConfigurationForTenant($this->tenant->id);

        // Assert - Should create configurations for all configurable dimensions
        $configurableDimensions = collect(AllocationDimensionType::cases())
            ->filter(fn ($dim) => $dim->isConfigurable())
        ;

        $this->assertCount(
            $configurableDimensions->count(),
            TenantDimensionConfiguration::where('tenant_id', $this->tenant->id)->get()
        );

        // Assert - RTR should not have configuration (it's always visible)
        $this->assertDatabaseMissing('tenant_dimension_configurations', [
            'tenant_id'      => $this->tenant->id,
            'dimension_type' => AllocationDimensionType::TRANSACTION_TYPE->value,
        ]);

        // Assert - Default enabled dimensions should be enabled
        foreach ($configurableDimensions as $dimension) {
            $config = TenantDimensionConfiguration::where('tenant_id', $this->tenant->id)
                ->where('dimension_type', $dimension)
                ->first()
            ;

            $this->assertNotNull($config);
            $this->assertEquals($dimension->getDefaultEnabledState(), $config->is_enabled);
            $this->assertEquals($dimension->getDefaultDisplayOrder(), $config->display_order);
        }
    }

    public function testGetEnabledDimensionsIncludesRtrAlways(): void
    {
        // Arrange - Initialize configurations
        $this->service->initializeDefaultConfigurationForTenant($this->tenant->id);

        // Act
        $enabledDimensions = $this->service->getEnabledDimensionsForTenant($this->tenant->id);

        // Assert - RTR should always be included first
        $this->assertTrue($enabledDimensions->contains(AllocationDimensionType::TRANSACTION_TYPE));
        $this->assertEquals(AllocationDimensionType::TRANSACTION_TYPE, $enabledDimensions->first());
    }

    public function testGetEnabledDimensionsReturnsOnlyEnabled(): void
    {
        // Arrange
        $this->service->initializeDefaultConfigurationForTenant($this->tenant->id);

        // Disable PROJECT dimension
        $this->service->updateDimensionConfiguration(
            $this->tenant->id,
            AllocationDimensionType::PROJECT,
            false
        );

        // Act
        $enabledDimensions = $this->service->getEnabledDimensionsForTenant($this->tenant->id);

        // Assert - PROJECT should not be included
        $this->assertFalse($enabledDimensions->contains(AllocationDimensionType::PROJECT));

        // Assert - RTR should still be included
        $this->assertTrue($enabledDimensions->contains(AllocationDimensionType::TRANSACTION_TYPE));
    }

    public function testUpdateDimensionConfiguration(): void
    {
        // Arrange
        $this->service->initializeDefaultConfigurationForTenant($this->tenant->id);

        // Act
        $this->service->updateDimensionConfiguration(
            $this->tenant->id,
            AllocationDimensionType::LOCATION,
            true,
            100
        );

        // Assert
        $config = TenantDimensionConfiguration::where('tenant_id', $this->tenant->id)
            ->where('dimension_type', AllocationDimensionType::LOCATION)
            ->first()
        ;

        $this->assertTrue($config->is_enabled);
        $this->assertEquals(100, $config->display_order);
    }

    public function testCannotConfigureRtrDimension(): void
    {
        // Assert - Should throw exception when trying to configure RTR
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Dimension RTR is not configurable');

        // Act
        $this->service->updateDimensionConfiguration(
            $this->tenant->id,
            AllocationDimensionType::TRANSACTION_TYPE,
            false
        );
    }

    public function testIsDimensionEnabledForTenant(): void
    {
        // Arrange
        $this->service->initializeDefaultConfigurationForTenant($this->tenant->id);

        // Act & Assert - RTR should always be enabled
        $this->assertTrue(
            $this->service->isDimensionEnabledForTenant(
                $this->tenant->id,
                AllocationDimensionType::TRANSACTION_TYPE
            )
        );

        // Act & Assert - PROJECT should be enabled by default
        $this->assertTrue(
            $this->service->isDimensionEnabledForTenant(
                $this->tenant->id,
                AllocationDimensionType::PROJECT
            )
        );

        // Disable PROJECT and test again
        $this->service->updateDimensionConfiguration(
            $this->tenant->id,
            AllocationDimensionType::PROJECT,
            false
        );

        $this->assertFalse(
            $this->service->isDimensionEnabledForTenant(
                $this->tenant->id,
                AllocationDimensionType::PROJECT
            )
        );
    }

    public function testGetAllDimensionsForTenant(): void
    {
        // Arrange
        $this->service->initializeDefaultConfigurationForTenant($this->tenant->id);

        // Act
        $allDimensions = $this->service->getAllDimensionsForTenant($this->tenant->id);

        // Assert - Should include all 11 dimensions
        $this->assertCount(11, $allDimensions);

        // Assert - Each dimension should have required fields
        foreach ($allDimensions as $dimensionData) {
            $this->assertArrayHasKey('dimension', $dimensionData);
            $this->assertArrayHasKey('is_enabled', $dimensionData);
            $this->assertArrayHasKey('display_order', $dimensionData);
            $this->assertArrayHasKey('is_always_visible', $dimensionData);
            $this->assertArrayHasKey('is_configurable', $dimensionData);

            $this->assertInstanceOf(AllocationDimensionType::class, $dimensionData['dimension']);
        }

        // Assert - RTR should be marked as always visible and not configurable
        $rtrData = $allDimensions->firstWhere('dimension', AllocationDimensionType::TRANSACTION_TYPE);
        $this->assertTrue($rtrData['is_always_visible']);
        $this->assertFalse($rtrData['is_configurable']);
    }

    public function testResetToDefaults(): void
    {
        // Arrange
        $this->service->initializeDefaultConfigurationForTenant($this->tenant->id);

        // Modify some configurations
        $this->service->updateDimensionConfiguration(
            $this->tenant->id,
            AllocationDimensionType::PROJECT,
            false,
            999
        );

        // Act
        $this->service->resetToDefaults($this->tenant->id);

        // Assert - Configuration should be back to defaults
        $config = TenantDimensionConfiguration::where('tenant_id', $this->tenant->id)
            ->where('dimension_type', AllocationDimensionType::PROJECT)
            ->first()
        ;

        $this->assertEquals(AllocationDimensionType::PROJECT->getDefaultEnabledState(), $config->is_enabled);
        $this->assertEquals(AllocationDimensionType::PROJECT->getDefaultDisplayOrder(), $config->display_order);
    }

    public function testBulkUpdateDimensionConfigurations(): void
    {
        // Arrange
        $this->service->initializeDefaultConfigurationForTenant($this->tenant->id);

        $configurations = [
            [
                'dimension_type' => AllocationDimensionType::PROJECT->value,
                'is_enabled'     => false,
                'display_order'  => 10,
            ],
            [
                'dimension_type' => AllocationDimensionType::EMPLOYEES->value,
                'is_enabled'     => true,
                'display_order'  => 5,
            ],
        ];

        // Act
        $this->service->bulkUpdateDimensionConfigurations($this->tenant->id, $configurations);

        // Assert
        $projectConfig = TenantDimensionConfiguration::where('tenant_id', $this->tenant->id)
            ->where('dimension_type', AllocationDimensionType::PROJECT)
            ->first()
        ;

        $employeesConfig = TenantDimensionConfiguration::where('tenant_id', $this->tenant->id)
            ->where('dimension_type', AllocationDimensionType::EMPLOYEES)
            ->first()
        ;

        $this->assertFalse($projectConfig->is_enabled);
        $this->assertEquals(10, $projectConfig->display_order);

        $this->assertTrue($employeesConfig->is_enabled);
        $this->assertEquals(5, $employeesConfig->display_order);
    }
}
