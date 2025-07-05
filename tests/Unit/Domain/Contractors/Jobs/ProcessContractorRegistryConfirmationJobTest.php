<?php

namespace Tests\Unit\Domain\Contractors\Jobs;

use App\Domain\Contractors\Jobs\ProcessContractorRegistryConfirmationJob;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Contractors\Services\ContractorRegistryConfirmationService;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Utils\Models\RegistryConfirmation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;
use Tests\Traits\WithAuthenticatedUser;

/**
 * @internal
 */
#[CoversClass(ProcessContractorRegistryConfirmationJob::class)]
class ProcessContractorRegistryConfirmationJobTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up tenant context
        $this->tenant = Tenant::factory()->create();
        $this->authenticateUser($this->tenant);
    }

    public function testHandleSuccessfullyProcessesConfirmation(): void
    {
        // Arrange
        $contractor = Tenant::bypassTenant($this->tenant->id, function () {
            return Contractor::factory()->create([
                'tenant_id' => $this->tenant->id,
                'name'      => 'Test Company',
            ]);
        });

        $confirmations = [
            RegistryConfirmation::factory()->make(['id' => 'test-1']),
            RegistryConfirmation::factory()->make(['id' => 'test-2']),
        ];

        $mockService = \Mockery::mock(ContractorRegistryConfirmationService::class);
        // @phpstan-ignore-next-line
        $mockService
            ->shouldReceive('confirm')
            ->once()
            ->with($contractor)
            ->andReturn($confirmations)
        ;

        Log::shouldReceive('info')
            ->once()
            ->with('Starting registry confirmation process', [
                'contractor_id'   => $contractor->id,
                'contractor_name' => $contractor->name,
            ])
        ;

        Log::shouldReceive('info')
            ->once()
            ->with('Registry confirmation process completed', [
                'contractor_id'       => $contractor->id,
                'confirmations_count' => 2,
                'confirmation_ids'    => ['test-1', 'test-2'],
            ])
        ;

        // Act
        $job = new ProcessContractorRegistryConfirmationJob($contractor);
        $job->handle($mockService);

        // Assert - expectations are verified by Mockery
        $this->assertTrue(true);
    }

    public function testHandleLogsErrorAndRethrowsException(): void
    {
        // Arrange
        $contractor = Tenant::bypassTenant($this->tenant->id, function () {
            return Contractor::factory()->create([
                'tenant_id' => $this->tenant->id,
                'name'      => 'Test Company',
            ]);
        });

        $exception = new \Exception('Registry API failed');

        $mockService = \Mockery::mock(ContractorRegistryConfirmationService::class);
        // @phpstan-ignore-next-line
        $mockService
            ->shouldReceive('confirm')
            ->once()
            ->with($contractor)
            ->andThrow($exception)
        ;

        Log::shouldReceive('info')
            ->once()
            ->with('Starting registry confirmation process', \Mockery::type('array'))
        ;

        Log::shouldReceive('error')
            ->once()
            ->with('Registry confirmation process failed', \Mockery::type('array'))
        ;

        // Act & Assert
        $job = new ProcessContractorRegistryConfirmationJob($contractor);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Registry API failed');

        $job->handle($mockService);
    }

    public function testFailedLogsErrorPermanently(): void
    {
        // Arrange
        $contractor = Tenant::bypassTenant($this->tenant->id, function () {
            return Contractor::factory()->create([
                'tenant_id' => $this->tenant->id,
                'name'      => 'Test Company',
            ]);
        });

        $exception = new \Exception('Permanent failure');

        Log::shouldReceive('error')
            ->once()
            ->with('Registry confirmation job failed permanently', [
                'contractor_id'   => $contractor->id,
                'contractor_name' => $contractor->name,
                'error'           => 'Permanent failure',
                'attempts'        => 1,
            ])
        ;

        // Act
        $job = new ProcessContractorRegistryConfirmationJob($contractor);
        $job->failed($exception);

        // Assert - expectations are verified by Mockery
        $this->assertTrue(true);
    }

    public function testJobConfiguration(): void
    {
        // Arrange
        $contractor = Tenant::bypassTenant($this->tenant->id, function () {
            return Contractor::factory()->create([
                'tenant_id' => $this->tenant->id,
            ]);
        });

        // Act
        $job = new ProcessContractorRegistryConfirmationJob($contractor);

        // Assert
        $this->assertEquals(3, $job->tries);
        $this->assertEquals(3, $job->maxExceptions);
        $this->assertEquals(120, $job->timeout);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
