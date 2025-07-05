<?php

namespace Tests\Unit\Domain\Contractors\Jobs;

use App\Domain\Contractors\Jobs\ProcessContractorRegistryConfirmationJob;
use App\Domain\Contractors\Models\Contractor;
use App\Domain\Contractors\Services\RegistryConfirmation\MfContractorRegistryConfirmationService;
use App\Domain\Contractors\Services\RegistryConfirmation\RegonContractorRegistryConfirmationService;
use App\Domain\Contractors\Services\RegistryConfirmation\ViesContractorRegistryConfirmationService;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Utils\DTOs\AllLookupResults;
use App\Domain\Utils\Enums\RegistryConfirmationStatus;
use App\Domain\Utils\Enums\RegistryConfirmationType;
use App\Domain\Utils\Models\RegistryConfirmation;
use App\Domain\Utils\Services\CompanyDataFetcherService;
use App\Services\RegonLookup\DTOs\RegonReportUnified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
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

    public function testHandleSuccessfullyProcessesRegonConfirmation(): void
    {
        // Arrange
        $contractor = Tenant::bypassTenant($this->tenant->id, function () {
            return Contractor::factory()->create([
                'tenant_id' => $this->tenant->id,
                'vat_id'    => '1234567890',
                'regon'     => '123456789',
                'country'   => 'PL',
            ]);
        });

        $confirmation = Tenant::bypassTenant($this->tenant->id, function () use ($contractor) {
            return RegistryConfirmation::create([
                'confirmable_id'   => $contractor->id,
                'confirmable_type' => get_class($contractor),
                'type'             => RegistryConfirmationType::Regon->value,
                'payload'          => ['nip' => '1234567890', 'regon' => '123456789', 'country' => 'PL'],
                'status'           => RegistryConfirmationStatus::Pending,
            ]);
        });

        $regonData = new RegonReportUnified(
            regon: '123456789',
            nip: '1234567890',
            nipStatus: null,
            name: 'Test Company',
            shortName: 'Test',
            registrationNumber: '0000123456',
            registrationDate: '2024-01-01',
            establishmentDate: '2024-01-01',
            businessStartDate: '2024-01-01',
            regonRegistrationDate: '2024-01-01',
            businessSuspensionDate: null,
            businessResumptionDate: null,
            lastChangeDate: '2024-01-01',
            businessEndDate: null,
            regonDeletionDate: null,
            bankruptcyDeclarationDate: null,
            bankruptcyEndDate: null,
            countryCode: 'PL',
            provinceCode: '14',
            countyCode: '01',
            municipalityCode: '001',
            postalCode: '00-000',
            postalCityCode: '001',
            cityCode: '001',
            streetCode: '001',
            buildingNumber: '1',
            apartmentNumber: null,
            unusualLocation: null,
            phoneNumber: null,
            internalPhoneNumber: null,
            faxNumber: null,
            email: null,
            website: null,
            countryName: 'POLAND',
            provinceName: 'TEST',
            countyName: 'TEST',
            municipalityName: 'TEST',
            cityName: 'TEST',
            postalCityName: 'TEST',
            streetName: 'TEST STREET',
            registrationDeletionDate: null,
            registrationAuthorityCode: '001',
            registryTypeCode: '001',
            registrationAuthorityName: 'TEST AUTHORITY',
            registryTypeName: 'TEST REGISTRY',
            legalFormCode: null,
            detailedLegalFormCode: null,
            financingFormCode: null,
            ownershipFormCode: null,
            foundingBodyCode: null,
            legalFormName: null,
            detailedLegalFormName: null,
            financingFormName: null,
            ownershipFormName: null,
            foundingBodyName: null,
            localUnitsCount: null,
            hasNotStartedActivity: null,
        );

        $allLookupResults = new AllLookupResults(
            regon: $regonData,
            mf: null,
            vies: null,
        );

        $mockDataFetcher  = \Mockery::mock(CompanyDataFetcherService::class);
        $mockRegonService = \Mockery::mock(RegonContractorRegistryConfirmationService::class);
        $mockViesService  = \Mockery::mock(ViesContractorRegistryConfirmationService::class);
        $mockMfService    = \Mockery::mock(MfContractorRegistryConfirmationService::class);

        // @phpstan-ignore-next-line
        $mockDataFetcher
            ->shouldReceive('fetch')
            ->once()
            ->andReturn($allLookupResults)
        ;

        // @phpstan-ignore-next-line
        $mockRegonService
            ->shouldReceive('confirmContractorData')
            ->once()
            ->with(\Mockery::type(Contractor::class), $regonData)
            ->andReturn([['type' => 'regon', 'success' => true]])
        ;

        Log::shouldReceive('info')
            ->twice()
        ;

        Log::shouldReceive('error')
            ->zeroOrMoreTimes()
        ;

        // Act
        $job = new ProcessContractorRegistryConfirmationJob($confirmation);
        $job->handle($mockDataFetcher, $mockRegonService, $mockViesService, $mockMfService);

        // Assert
        $this->assertDatabaseHas('registry_confirmations', [
            'id'     => $confirmation->id,
            'status' => RegistryConfirmationStatus::Success->value,
        ]);
    }

    public function testHandleFailsWhenNoDataAvailable(): void
    {
        // Arrange
        $contractor = Tenant::bypassTenant($this->tenant->id, function () {
            return Contractor::factory()->create([
                'tenant_id' => $this->tenant->id,
                'vat_id'    => '1234567890',
                'regon'     => '123456789',
                'country'   => 'PL',
            ]);
        });

        $confirmation = Tenant::bypassTenant($this->tenant->id, function () use ($contractor) {
            return RegistryConfirmation::create([
                'confirmable_id'   => $contractor->id,
                'confirmable_type' => get_class($contractor),
                'type'             => RegistryConfirmationType::Regon->value,
                'payload'          => ['nip' => '1234567890', 'regon' => '123456789', 'country' => 'PL'],
                'status'           => RegistryConfirmationStatus::Pending,
            ]);
        });

        $mockDataFetcher  = \Mockery::mock(CompanyDataFetcherService::class);
        $mockRegonService = \Mockery::mock(RegonContractorRegistryConfirmationService::class);
        $mockViesService  = \Mockery::mock(ViesContractorRegistryConfirmationService::class);
        $mockMfService    = \Mockery::mock(MfContractorRegistryConfirmationService::class);

        // @phpstan-ignore-next-line
        $mockDataFetcher
            ->shouldReceive('fetch')
            ->once()
            ->andReturn(null)
        ;

        Log::shouldReceive('info')
            ->once()
        ;

        Log::shouldReceive('error')
            ->once()
        ;

        // Act & Assert
        $job = new ProcessContractorRegistryConfirmationJob($confirmation);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No data available from external registries');

        $job->handle($mockDataFetcher, $mockRegonService, $mockViesService, $mockMfService);

        // Assert failure was recorded
        $this->assertDatabaseHas('registry_confirmations', [
            'id'     => $confirmation->id,
            'status' => RegistryConfirmationStatus::Failed->value,
        ]);
    }

    public function testFailedUpdatesConfirmationStatus(): void
    {
        // Arrange
        $contractor = Tenant::bypassTenant($this->tenant->id, function () {
            return Contractor::factory()->create([
                'tenant_id' => $this->tenant->id,
                'vat_id'    => '1234567890',
                'regon'     => '123456789',
                'country'   => 'PL',
            ]);
        });

        $confirmation = Tenant::bypassTenant($this->tenant->id, function () use ($contractor) {
            return RegistryConfirmation::create([
                'confirmable_id'   => $contractor->id,
                'confirmable_type' => get_class($contractor),
                'type'             => RegistryConfirmationType::Regon->value,
                'payload'          => ['nip' => '1234567890', 'regon' => '123456789', 'country' => 'PL'],
                'status'           => RegistryConfirmationStatus::Pending,
            ]);
        });

        $exception = new \Exception('Permanent failure');

        Log::shouldReceive('error')
            ->once()
            ->with('Registry confirmation job permanently failed', \Mockery::type('array'))
        ;

        // Act
        $job = new ProcessContractorRegistryConfirmationJob($confirmation);
        $job->failed($exception);

        // Assert
        $this->assertDatabaseHas('registry_confirmations', [
            'id'     => $confirmation->id,
            'status' => RegistryConfirmationStatus::Failed->value,
        ]);
    }

    public function testJobConfiguration(): void
    {
        // Arrange
        $contractor = Tenant::bypassTenant($this->tenant->id, function () {
            return Contractor::factory()->create([
                'tenant_id' => $this->tenant->id,
            ]);
        });

        $confirmation = Tenant::bypassTenant($this->tenant->id, function () use ($contractor) {
            return RegistryConfirmation::create([
                'confirmable_id'   => $contractor->id,
                'confirmable_type' => get_class($contractor),
                'type'             => RegistryConfirmationType::Regon->value,
                'payload'          => ['nip' => '1234567890'],
                'status'           => RegistryConfirmationStatus::Pending,
            ]);
        });

        // Act
        $job = new ProcessContractorRegistryConfirmationJob($confirmation);

        // Assert
        $this->assertEquals(3, $job->tries);
        $this->assertEquals(120, $job->timeout);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
