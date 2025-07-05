<?php

namespace Tests\Unit\Domain\Contractors\Services;

use App\Domain\Contractors\Models\Contractor;
use App\Domain\Contractors\Services\ContractorRegistryConfirmationService;
use App\Domain\Contractors\Services\RegistryConfirmation\MfContractorRegistryConfirmationService;
use App\Domain\Contractors\Services\RegistryConfirmation\RegonContractorRegistryConfirmationService;
use App\Domain\Contractors\Services\RegistryConfirmation\ViesContractorRegistryConfirmationService;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Utils\DTOs\AllLookupResults;
use App\Domain\Utils\DTOs\CompanyContext;
use App\Domain\Utils\Models\RegistryConfirmation;
use App\Domain\Utils\Services\CompanyDataFetcherService;
use App\Services\MfLookup\DTOs\MfLookupResultDTO;
use App\Services\RegonLookup\DTOs\RegonReportUnified;
use App\Services\ViesLookup\DTOs\ViesLookupResultDTO;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;
use Tests\Traits\WithAuthenticatedUser;

/**
 * @internal
 */
#[CoversClass(ContractorRegistryConfirmationService::class)]
class ContractorRegistryConfirmationServiceTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;

    private ContractorRegistryConfirmationService $service;

    private Mockery\MockInterface $dataFetcherService;

    private Mockery\MockInterface $regonService;

    private Mockery\MockInterface $viesService;

    private Mockery\MockInterface $mfService;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up tenant context
        $this->tenant = Tenant::factory()->create();
        $this->authenticateUser($this->tenant);

        // Create mocks
        $this->dataFetcherService = \Mockery::mock(CompanyDataFetcherService::class);
        $this->regonService       = \Mockery::mock(RegonContractorRegistryConfirmationService::class);
        $this->viesService        = \Mockery::mock(ViesContractorRegistryConfirmationService::class);
        $this->mfService          = \Mockery::mock(MfContractorRegistryConfirmationService::class);

        // Bind mocks to container
        $this->app->instance(CompanyDataFetcherService::class, $this->dataFetcherService);
        $this->app->instance(RegonContractorRegistryConfirmationService::class, $this->regonService);
        $this->app->instance(ViesContractorRegistryConfirmationService::class, $this->viesService);
        $this->app->instance(MfContractorRegistryConfirmationService::class, $this->mfService);

        // Create service instance
        $this->service = $this->app->make(ContractorRegistryConfirmationService::class);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    public function testConfirmSuccessfullyProcessesAllRegistries(): void
    {
        // Arrange
        $contractor = Tenant::bypassTenant($this->tenant->id, function () {
            return Contractor::factory()->create([
                'vat_id'    => '1234567890',
                'regon'     => '123456789',
                'country'   => 'PL',
                'tenant_id' => $this->tenant->id,
            ]);
        });

        // Create simple test DTOs with minimal required data
        $regonData = new RegonReportUnified(
            regon: '123456789',
            nip: '1234567890',
            nipStatus: null,
            name: 'Test Company',
            shortName: null,
            registrationNumber: '123456',
            registrationDate: '2020-01-01',
            establishmentDate: '2020-01-01',
            businessStartDate: '2020-01-01',
            regonRegistrationDate: null,
            businessSuspensionDate: null,
            businessResumptionDate: null,
            lastChangeDate: '2020-01-01',
            businessEndDate: null,
            regonDeletionDate: null,
            bankruptcyDeclarationDate: null,
            bankruptcyEndDate: null,
            countryCode: 'PL',
            provinceCode: '14',
            countyCode: '34',
            municipalityCode: '021',
            postalCode: '00-001',
            postalCityCode: '123',
            cityCode: '123',
            streetCode: '456',
            buildingNumber: '1',
            apartmentNumber: null,
            unusualLocation: null,
            phoneNumber: null,
            internalPhoneNumber: null,
            faxNumber: null,
            email: null,
            website: null,
            countryName: 'POLSKA',
            provinceName: 'MAZOWIECKIE',
            countyName: 'Test County',
            municipalityName: 'Test Municipality',
            cityName: 'Warsaw',
            postalCityName: 'Warsaw',
            streetName: 'Test Street',
            registrationDeletionDate: null,
            registrationAuthorityCode: '123',
            registryTypeCode: '456',
            registrationAuthorityName: 'Test Authority',
            registryTypeName: 'Test Registry',
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

        $viesData = \Mockery::mock(ViesLookupResultDTO::class);
        $mfData   = \Mockery::mock(MfLookupResultDTO::class);

        $allLookupResults = new AllLookupResults(
            regon: $regonData,
            mf: $mfData,
            vies: $viesData
        );

        $regonConfirmation = new RegistryConfirmation(['id' => 'regon-1']);
        $viesConfirmation  = new RegistryConfirmation(['id' => 'vies-1']);
        $mfConfirmation    = new RegistryConfirmation(['id' => 'mf-1']);

        // @phpstan-ignore-next-line
        $this->dataFetcherService
            ->shouldReceive('fetch')
            ->once()
            ->with(\Mockery::type(CompanyContext::class))
            ->andReturn($allLookupResults)
        ;

        // @phpstan-ignore-next-line
        $this->regonService
            ->shouldReceive('confirmContractorData')
            ->once()
            ->with($contractor, $regonData)
            ->andReturn([$regonConfirmation])
        ;

        // @phpstan-ignore-next-line
        $this->viesService
            ->shouldReceive('confirmContractorData')
            ->once()
            ->with($contractor, $viesData)
            ->andReturn([$viesConfirmation])
        ;

        // @phpstan-ignore-next-line
        $this->mfService
            ->shouldReceive('confirmContractorData')
            ->once()
            ->with($contractor, $mfData)
            ->andReturn([$mfConfirmation])
        ;

        // Act
        $result = $this->service->confirm($contractor);

        // Assert
        $this->assertCount(3, $result);
        $this->assertContains($regonConfirmation, $result);
        $this->assertContains($viesConfirmation, $result);
        $this->assertContains($mfConfirmation, $result);
    }

    public function testConfirmHandlesNoRegistryData(): void
    {
        // Arrange
        $contractor = Tenant::bypassTenant($this->tenant->id, function () {
            return Contractor::factory()->create([
                'vat_id'    => '1234567890',
                'regon'     => '123456789',
                'country'   => 'PL',
                'tenant_id' => $this->tenant->id,
            ]);
        });

        Log::shouldReceive('warning')
            ->once()
            ->with('No registry data found for contractor', \Mockery::type('array'))
        ;

        // @phpstan-ignore-next-line
        $this->dataFetcherService
            ->shouldReceive('fetch')
            ->once()
            ->andReturn(null)
        ;

        // Act
        $result = $this->service->confirm($contractor);

        // Assert
        $this->assertEmpty($result);
    }

    public function testConfirmHandlesPartialRegistryData(): void
    {
        // Arrange
        $contractor = Tenant::bypassTenant($this->tenant->id, function () {
            return Contractor::factory()->create([
                'vat_id'    => '1234567890',
                'regon'     => '123456789',
                'country'   => 'PL',
                'tenant_id' => $this->tenant->id,
            ]);
        });

        $regonData = new RegonReportUnified(
            regon: '123456789',
            nip: '1234567890',
            nipStatus: null,
            name: 'Test Company',
            shortName: null,
            registrationNumber: '123456',
            registrationDate: '2020-01-01',
            establishmentDate: '2020-01-01',
            businessStartDate: '2020-01-01',
            regonRegistrationDate: null,
            businessSuspensionDate: null,
            businessResumptionDate: null,
            lastChangeDate: '2020-01-01',
            businessEndDate: null,
            regonDeletionDate: null,
            bankruptcyDeclarationDate: null,
            bankruptcyEndDate: null,
            countryCode: 'PL',
            provinceCode: '14',
            countyCode: '34',
            municipalityCode: '021',
            postalCode: '00-001',
            postalCityCode: '123',
            cityCode: '123',
            streetCode: '456',
            buildingNumber: '1',
            apartmentNumber: null,
            unusualLocation: null,
            phoneNumber: null,
            internalPhoneNumber: null,
            faxNumber: null,
            email: null,
            website: null,
            countryName: 'POLSKA',
            provinceName: 'MAZOWIECKIE',
            countyName: 'Test County',
            municipalityName: 'Test Municipality',
            cityName: 'Warsaw',
            postalCityName: 'Warsaw',
            streetName: 'Test Street',
            registrationDeletionDate: null,
            registrationAuthorityCode: '123',
            registryTypeCode: '456',
            registrationAuthorityName: 'Test Authority',
            registryTypeName: 'Test Registry',
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
            vies: null
        );

        $regonConfirmation = new RegistryConfirmation(['id' => 'regon-1']);

        // @phpstan-ignore-next-line
        $this->dataFetcherService
            ->shouldReceive('fetch')
            ->once()
            ->andReturn($allLookupResults)
        ;

        // @phpstan-ignore-next-line
        $this->regonService
            ->shouldReceive('confirmContractorData')
            ->once()
            ->with($contractor, $regonData)
            ->andReturn([$regonConfirmation])
        ;

        // viesService and mfService should not be called

        // Act
        $result = $this->service->confirm($contractor);

        // Assert
        $this->assertCount(1, $result);
        $this->assertContains($regonConfirmation, $result);
    }

    public function testConfirmHandlesRegonServiceException(): void
    {
        // Arrange
        $contractor = Tenant::bypassTenant($this->tenant->id, function () {
            return Contractor::factory()->create([
                'vat_id'    => '1234567890',
                'regon'     => '123456789',
                'country'   => 'PL',
                'tenant_id' => $this->tenant->id,
            ]);
        });

        $regonData = new RegonReportUnified(
            regon: '123456789',
            nip: '1234567890',
            nipStatus: null,
            name: 'Test Company',
            shortName: null,
            registrationNumber: '123456',
            registrationDate: '2020-01-01',
            establishmentDate: '2020-01-01',
            businessStartDate: '2020-01-01',
            regonRegistrationDate: null,
            businessSuspensionDate: null,
            businessResumptionDate: null,
            lastChangeDate: '2020-01-01',
            businessEndDate: null,
            regonDeletionDate: null,
            bankruptcyDeclarationDate: null,
            bankruptcyEndDate: null,
            countryCode: 'PL',
            provinceCode: '14',
            countyCode: '34',
            municipalityCode: '021',
            postalCode: '00-001',
            postalCityCode: '123',
            cityCode: '123',
            streetCode: '456',
            buildingNumber: '1',
            apartmentNumber: null,
            unusualLocation: null,
            phoneNumber: null,
            internalPhoneNumber: null,
            faxNumber: null,
            email: null,
            website: null,
            countryName: 'POLSKA',
            provinceName: 'MAZOWIECKIE',
            countyName: 'Test County',
            municipalityName: 'Test Municipality',
            cityName: 'Warsaw',
            postalCityName: 'Warsaw',
            streetName: 'Test Street',
            registrationDeletionDate: null,
            registrationAuthorityCode: '123',
            registryTypeCode: '456',
            registrationAuthorityName: 'Test Authority',
            registryTypeName: 'Test Registry',
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

        $viesData = \Mockery::mock(ViesLookupResultDTO::class);

        $allLookupResults = new AllLookupResults(
            regon: $regonData,
            mf: null,
            vies: $viesData
        );

        $viesConfirmation = new RegistryConfirmation(['id' => 'vies-1']);

        // @phpstan-ignore-next-line
        $this->dataFetcherService
            ->shouldReceive('fetch')
            ->once()
            ->andReturn($allLookupResults)
        ;

        // @phpstan-ignore-next-line
        $this->regonService
            ->shouldReceive('confirmContractorData')
            ->once()
            ->andThrow(new \Exception('REGON service error'))
        ;

        // @phpstan-ignore-next-line
        $this->viesService
            ->shouldReceive('confirmContractorData')
            ->once()
            ->andReturn([$viesConfirmation])
        ;

        Log::shouldReceive('error')
            ->once()
            ->with('Error processing REGON confirmations', \Mockery::type('array'))
        ;

        Log::shouldReceive('info')
            ->once()
            ->with('Registry confirmations completed', \Mockery::type('array'))
        ;

        // Act
        $result = $this->service->confirm($contractor);

        // Assert
        $this->assertCount(1, $result);
        $this->assertContains($viesConfirmation, $result);
    }

    public function testConfirmHandlesDataFetcherException(): void
    {
        // Arrange
        $contractor = Tenant::bypassTenant($this->tenant->id, function () {
            return Contractor::factory()->create([
                'vat_id'    => '1234567890',
                'regon'     => '123456789',
                'country'   => 'PL',
                'tenant_id' => $this->tenant->id,
            ]);
        });

        // @phpstan-ignore-next-line
        $this->dataFetcherService
            ->shouldReceive('fetch')
            ->once()
            ->andThrow(new \Exception('Data fetcher error'))
        ;

        Log::shouldReceive('error')
            ->once()
            ->with('Error during registry confirmation process', \Mockery::type('array'))
        ;

        // Act
        $result = $this->service->confirm($contractor);

        // Assert
        $this->assertEmpty($result);
    }

    public function testGetConfirmationsReturnsOrderedResults(): void
    {
        // Arrange
        $contractor = Tenant::bypassTenant($this->tenant->id, function () {
            return Contractor::factory()->create([
                'tenant_id' => $this->tenant->id,
            ]);
        });

        // Create registry confirmations with different timestamps
        $confirmation1 = RegistryConfirmation::factory()->create([
            'confirmable_id'   => $contractor->id,
            'confirmable_type' => get_class($contractor),
            'checked_at'       => now()->subDays(2),
        ]);

        $confirmation2 = RegistryConfirmation::factory()->create([
            'confirmable_id'   => $contractor->id,
            'confirmable_type' => get_class($contractor),
            'checked_at'       => now()->subDay(),
        ]);

        // Act
        $result = $this->service->getConfirmations($contractor);

        // Assert
        $this->assertCount(2, $result);
        // Should be ordered by checked_at desc (newest first)
        // @phpstan-ignore-next-line
        $this->assertEquals($confirmation2->id, $result->first()->id);
        // @phpstan-ignore-next-line
        $this->assertEquals($confirmation1->id, $result->last()->id);
    }

    public function testGetLatestConfirmationReturnsCorrectResult(): void
    {
        // Arrange
        $contractor = Tenant::bypassTenant($this->tenant->id, function () {
            return Contractor::factory()->create([
                'tenant_id' => $this->tenant->id,
            ]);
        });

        $olderConfirmation = RegistryConfirmation::factory()->create([
            'confirmable_id'   => $contractor->id,
            'confirmable_type' => get_class($contractor),
            'type'             => 'regon',
            'checked_at'       => now()->subDays(2),
        ]);

        $newerConfirmation = RegistryConfirmation::factory()->create([
            'confirmable_id'   => $contractor->id,
            'confirmable_type' => get_class($contractor),
            'type'             => 'regon',
            'checked_at'       => now()->subDay(),
        ]);

        // Different type should not be returned
        RegistryConfirmation::factory()->create([
            'confirmable_id'   => $contractor->id,
            'confirmable_type' => get_class($contractor),
            'type'             => 'vies',
            'checked_at'       => now(),
        ]);

        // Act
        $result = $this->service->getLatestConfirmation($contractor, 'regon');

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals($newerConfirmation->id, $result->id);
    }

    public function testGetLatestConfirmationReturnsNullWhenNotFound(): void
    {
        // Arrange
        $contractor = Tenant::bypassTenant($this->tenant->id, function () {
            return Contractor::factory()->create([
                'tenant_id' => $this->tenant->id,
            ]);
        });

        // Act
        $result = $this->service->getLatestConfirmation($contractor, 'regon');

        // Assert
        $this->assertNull($result);
    }

    public function testHasSuccessfulConfirmationsReturnsTrueWhenExists(): void
    {
        // Arrange
        $contractor = Tenant::bypassTenant($this->tenant->id, function () {
            return Contractor::factory()->create([
                'tenant_id' => $this->tenant->id,
            ]);
        });

        RegistryConfirmation::factory()->create([
            'confirmable_id'   => $contractor->id,
            'confirmable_type' => get_class($contractor),
            'success'          => true,
        ]);

        // Act
        $result = $this->service->hasSuccessfulConfirmations($contractor);

        // Assert
        $this->assertTrue($result);
    }

    public function testHasSuccessfulConfirmationsReturnsFalseWhenNoneExist(): void
    {
        // Arrange
        $contractor = Tenant::bypassTenant($this->tenant->id, function () {
            return Contractor::factory()->create([
                'tenant_id' => $this->tenant->id,
            ]);
        });

        // Create failed confirmation
        RegistryConfirmation::factory()->create([
            'confirmable_id'   => $contractor->id,
            'confirmable_type' => get_class($contractor),
            'success'          => false,
        ]);

        // Act
        $result = $this->service->hasSuccessfulConfirmations($contractor);

        // Assert
        $this->assertFalse($result);
    }

    public function testHasSuccessfulConfirmationsReturnsFalseWhenNoConfirmations(): void
    {
        // Arrange
        $contractor = Tenant::bypassTenant($this->tenant->id, function () {
            return Contractor::factory()->create([
                'tenant_id' => $this->tenant->id,
            ]);
        });

        // Act
        $result = $this->service->hasSuccessfulConfirmations($contractor);

        // Assert
        $this->assertFalse($result);
    }
}
