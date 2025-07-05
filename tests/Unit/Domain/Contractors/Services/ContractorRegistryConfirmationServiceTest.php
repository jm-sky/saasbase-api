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
use App\Services\MfLookup\Enums\VatStatusEnum;
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

    public function testConfirmSuccessfullyProcessesAllRegistries()
    {
        $contractor = Tenant::bypassTenant($this->tenant->id, function () {
            return Contractor::factory()->create([
                'vat_id'    => '1234567890',
                'regon'     => '123456789',
                'country'   => 'PL',
                'tenant_id' => $this->tenant->id,
            ]);
        });

        $regonData = new RegonReportUnified(
            regon: '12345678901234',
            nip: '1234567890',
            nipStatus: null,
            name: 'Test Company',
            shortName: 'Test',
            registrationNumber: '0000123456',
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

        $viesData = new ViesLookupResultDTO(
            valid: true,
            countryCode: 'PL',
            vatNumber: '1234567890',
            requestDate: '2024-01-01',
            name: 'Test Company',
            address: 'Test Address',
            rawAddress: 'Test Address',
            cache: null,
        );

        $mfData = new MfLookupResultDTO(
            name: 'Test Company',
            nip: '1234567890',
            regon: '12345678901234',
            krs: '0000123456',
            residenceAddress: 'Test Address',
            workingAddress: 'Test Address',
            accountNumbers: ['PL10105000997603123456789123'],
            vatStatus: VatStatusEnum::ACTIVE,
            hasVirtualAccounts: false,
            representatives: [],
            authorizedClerks: [],
            partners: [],
            registrationLegalDate: '2024-01-01',
            cache: null,
        );

        $allLookupResults = new AllLookupResults(
            regon: $regonData,
            vies: $viesData,
            mf: $mfData,
        );

        $regonConfirmation = new RegistryConfirmation(['id' => 1, 'type' => 'regon']);
        $viesConfirmation  = new RegistryConfirmation(['id' => 2, 'type' => 'vies']);
        $mfConfirmation    = new RegistryConfirmation(['id' => 3, 'type' => 'mf']);

        // @phpstan-ignore-next-line
        $this->dataFetcherService->shouldReceive('fetch')
            ->with(\Mockery::type(CompanyContext::class))
            ->andReturn($allLookupResults)
        ;

        // @phpstan-ignore-next-line
        $this->regonService->shouldReceive('confirmContractorData')
            ->with($contractor, $regonData)
            ->andReturn([$regonConfirmation])
        ;

        // @phpstan-ignore-next-line
        $this->viesService->shouldReceive('confirmContractorData')
            ->with($contractor, $viesData)
            ->andReturn([$viesConfirmation])
        ;

        // @phpstan-ignore-next-line
        $this->mfService->shouldReceive('confirmContractorData')
            ->with($contractor, $mfData)
            ->andReturn([$mfConfirmation])
        ;

        $result = $this->service->confirm($contractor);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertContains($regonConfirmation, $result);
        $this->assertContains($viesConfirmation, $result);
        $this->assertContains($mfConfirmation, $result);
    }

    public function testConfirmHandlesNoRegistryData()
    {
        $contractor = Tenant::bypassTenant($this->tenant->id, function () {
            return Contractor::factory()->create([
                'vat_id'    => '1234567890',
                'regon'     => '123456789',
                'country'   => 'PL',
                'tenant_id' => $this->tenant->id,
            ]);
        });

        // @phpstan-ignore-next-line
        $this->dataFetcherService->shouldReceive('fetch')
            ->with(\Mockery::type(CompanyContext::class))
            ->andReturn(null)
        ;

        $result = $this->service->confirm($contractor);

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function testConfirmHandlesPartialRegistryData()
    {
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
            vies: null,
            mf: null,
        );

        $regonConfirmation = new RegistryConfirmation(['id' => 1, 'type' => 'regon']);

        // @phpstan-ignore-next-line
        $this->dataFetcherService->shouldReceive('fetch')
            ->with(\Mockery::type(CompanyContext::class))
            ->andReturn($allLookupResults)
        ;

        // @phpstan-ignore-next-line
        $this->regonService->shouldReceive('confirmContractorData')
            ->with($contractor, $regonData)
            ->andReturn([$regonConfirmation])
        ;

        $result = $this->service->confirm($contractor);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertContains($regonConfirmation, $result);
    }

    public function testConfirmHandlesRegonServiceException()
    {
        $contractor = Tenant::bypassTenant($this->tenant->id, function () {
            return Contractor::factory()->create([
                'vat_id'    => '1234567890',
                'regon'     => '123456789',
                'country'   => 'PL',
                'tenant_id' => $this->tenant->id,
            ]);
        });

        $regonData = new RegonReportUnified(
            regon: '12345678901234',
            nip: '1234567890',
            nipStatus: null,
            name: 'Test Company',
            shortName: 'Test',
            registrationNumber: '0000123456',
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

        $viesData = new ViesLookupResultDTO(
            valid: true,
            countryCode: 'PL',
            vatNumber: '1234567890',
            requestDate: '2024-01-01',
            name: 'Test Company',
            address: 'Test Address',
            rawAddress: 'Test Address',
            cache: null,
        );

        $allLookupResults = new AllLookupResults(
            regon: $regonData,
            vies: $viesData,
            mf: null,
        );

        $viesConfirmation = new RegistryConfirmation(['id' => 2, 'type' => 'vies']);

        // @phpstan-ignore-next-line
        $this->dataFetcherService->shouldReceive('fetch')
            ->with(\Mockery::type(CompanyContext::class))
            ->andReturn($allLookupResults)
        ;

        // @phpstan-ignore-next-line
        $this->regonService->shouldReceive('confirmContractorData')
            ->with($contractor, $regonData)
            ->andThrow(new \Exception('Registry service error'))
        ;

        // @phpstan-ignore-next-line
        $this->viesService->shouldReceive('confirmContractorData')
            ->with($contractor, $viesData)
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

        $result = $this->service->confirm($contractor);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertContains($viesConfirmation, $result);
    }

    public function testConfirmHandlesDataFetcherException()
    {
        $contractor = Tenant::bypassTenant($this->tenant->id, function () {
            return Contractor::factory()->create([
                'vat_id'    => '1234567890',
                'regon'     => '123456789',
                'country'   => 'PL',
                'tenant_id' => $this->tenant->id,
            ]);
        });

        // @phpstan-ignore-next-line
        $this->dataFetcherService->shouldReceive('fetch')
            ->with(\Mockery::type(CompanyContext::class))
            ->andThrow(new \Exception('Data fetcher error'))
        ;

        Log::shouldReceive('error')
            ->once()
            ->with('Error during registry confirmation process', \Mockery::type('array'))
        ;

        $result = $this->service->confirm($contractor);

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function testGetConfirmationsReturnsOrderedResults()
    {
        $contractor = Tenant::bypassTenant($this->tenant->id, function () {
            return Contractor::factory()->create([
                'tenant_id' => $this->tenant->id,
            ]);
        });

        $result = $this->service->getConfirmations($contractor);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $result);
    }

    public function testGetLatestConfirmationReturnsCorrectResult()
    {
        $contractor = Tenant::bypassTenant($this->tenant->id, function () {
            return Contractor::factory()->create([
                'tenant_id' => $this->tenant->id,
            ]);
        });

        $result = $this->service->getLatestConfirmation($contractor, 'regon');

        $this->assertNull($result);
    }

    public function testGetLatestConfirmationReturnsNullWhenNotFound()
    {
        $contractor = Tenant::bypassTenant($this->tenant->id, function () {
            return Contractor::factory()->create([
                'tenant_id' => $this->tenant->id,
            ]);
        });

        $result = $this->service->getLatestConfirmation($contractor, 'nonexistent');

        $this->assertNull($result);
    }

    public function testHasSuccessfulConfirmationsReturnsTrueWhenExists()
    {
        $contractor = Tenant::bypassTenant($this->tenant->id, function () {
            return Contractor::factory()->create([
                'tenant_id' => $this->tenant->id,
            ]);
        });

        $result = $this->service->hasSuccessfulConfirmations($contractor);

        $this->assertFalse($result);
    }

    public function testHasSuccessfulConfirmationsReturnsFalseWhenNoneExist()
    {
        $contractor = Tenant::bypassTenant($this->tenant->id, function () {
            return Contractor::factory()->create([
                'tenant_id' => $this->tenant->id,
            ]);
        });

        $result = $this->service->hasSuccessfulConfirmations($contractor);

        $this->assertFalse($result);
    }

    public function testHasSuccessfulConfirmationsReturnsFalseWhenNoConfirmations()
    {
        $contractor = Tenant::bypassTenant($this->tenant->id, function () {
            return Contractor::factory()->create([
                'tenant_id' => $this->tenant->id,
            ]);
        });

        $result = $this->service->hasSuccessfulConfirmations($contractor);

        $this->assertFalse($result);
    }
}
