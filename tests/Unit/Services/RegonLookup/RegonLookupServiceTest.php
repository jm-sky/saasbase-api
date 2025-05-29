<?php

namespace Tests\Unit\Services\RegonLookup;

use App\Services\RegonLookup\DTOs\RegonFullReportResultDTO;
use App\Services\RegonLookup\DTOs\RegonLookupResultDTO;
use App\Services\RegonLookup\Exceptions\RegonLookupException;
use App\Services\RegonLookup\Integrations\RegonApiConnector;
use App\Services\RegonLookup\Integrations\Requests\GetFullReportRequest;
use App\Services\RegonLookup\Integrations\Requests\SearchRequest;
use App\Services\RegonLookup\Services\RegonLookupService;
use Illuminate\Support\Facades\Cache;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\CoversClass;
use Saloon\Http\Response;
use Tests\TestCase;

/**
 * @internal
 */
#[CoversClass(RegonLookupService::class)]
class RegonLookupServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private RegonApiConnector $apiConnector;

    private RegonLookupService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->markTestAsSkipped();

        $this->apiConnector = \Mockery::mock(RegonApiConnector::class);
        $this->service      = new RegonLookupService($this->apiConnector);

        // Clear cache before each test
        Cache::flush();
    }

    public function testFindByNipReturnsNullWhenSearchFails(): void
    {
        $nip = '1234567890';

        $this->apiConnector
            ->shouldReceive('send')
            ->with(\Mockery::type(SearchRequest::class))
            ->andThrow(new \Exception('API Error'))
        ;

        $result = $this->service->findByNip($nip);

        $this->assertNull($result);
    }

    public function testFindByNipReturnsNullWhenSearchResultIsInvalid(): void
    {
        $nip = '1234567890';

        $searchResponse = \Mockery::mock(Response::class);
        $searchResponse->shouldReceive('dto')->andReturn(null);

        $this->apiConnector
            ->shouldReceive('send')
            ->with(\Mockery::type(SearchRequest::class))
            ->andReturn($searchResponse)
        ;

        $result = $this->service->findByNip($nip);

        $this->assertNull($result);
    }

    public function testFindByNipReturnsFullReportWhenSearchSucceeds(): void
    {
        $nip   = '1234567890';
        $regon = '123456789';

        // Mock search response
        $searchResult = new RegonLookupResultDTO(
            regon: $regon,
            nip: $nip,
            name: 'Test Company',
            shortName: null,
            registrationNumber: null,
            registrationDate: null,
            startDate: null,
            endDate: null,
            phoneNumber: null,
            email: null,
            website: null,
            address: null
        );

        $searchResponse = \Mockery::mock(Response::class);
        $searchResponse->shouldReceive('dto')->andReturn($searchResult);

        // Mock full report response
        $fullReportResult = new RegonFullReportResultDTO(
            regon: $regon,
            nip: $nip,
            nipStatus: null,
            name: 'Test Company',
            shortName: null,
            registrationNumber: '',
            registrationDate: '',
            establishmentDate: '',
            businessStartDate: '',
            regonRegistrationDate: null,
            businessSuspensionDate: null,
            businessResumptionDate: null,
            lastChangeDate: '',
            businessEndDate: null,
            regonDeletionDate: null,
            bankruptcyDeclarationDate: null,
            bankruptcyEndDate: null,
            countryCode: 'PL',
            provinceCode: '',
            countyCode: '',
            municipalityCode: '',
            postalCode: '00-000',
            postalCityCode: '',
            cityCode: '',
            streetCode: '',
            buildingNumber: '',
            apartmentNumber: null,
            unusualLocation: null,
            phoneNumber: '',
            internalPhoneNumber: null,
            faxNumber: null,
            email: null,
            website: null,
            countryName: 'Polska',
            provinceName: '',
            countyName: '',
            municipalityName: '',
            cityName: '',
            postalCityName: '',
            streetName: '',
            legalFormCode: '',
            detailedLegalFormCode: '',
            financingFormCode: '',
            ownershipFormCode: '',
            foundingBodyCode: null,
            registrationAuthorityCode: '',
            registryTypeCode: '',
            legalFormName: '',
            detailedLegalFormName: '',
            financingFormName: '',
            ownershipFormName: '',
            foundingBodyName: null,
            registrationAuthorityName: '',
            registryTypeName: '',
            localUnitsCount: 0
        );

        $fullReportResponse = \Mockery::mock(Response::class);
        $fullReportResponse->shouldReceive('dto')->andReturn($fullReportResult);

        $this->apiConnector
            ->shouldReceive('send')
            ->with(\Mockery::type(SearchRequest::class))
            ->andReturn($searchResponse)
        ;

        $this->apiConnector
            ->shouldReceive('send')
            ->with(\Mockery::type(GetFullReportRequest::class))
            ->andReturn($fullReportResponse)
        ;

        $result = $this->service->findByNip($nip);

        $this->assertInstanceOf(RegonFullReportResultDTO::class, $result);
        $this->assertEquals($regon, $result->regon);
        $this->assertEquals($nip, $result->nip);
        $this->assertEquals('Test Company', $result->name);
    }

    public function testFindByRegonReturnsNullWhenRequestFails(): void
    {
        $regon = '123456789';

        $this->apiConnector
            ->shouldReceive('send')
            ->with(\Mockery::type(GetFullReportRequest::class))
            ->andThrow(new \Exception('API Error'))
        ;

        $result = $this->service->findByRegon($regon);

        $this->assertNull($result);
    }

    public function testFindByRegonReturnsFullReportWhenRequestSucceeds(): void
    {
        $regon = '123456789';

        $fullReportResult = new RegonFullReportResultDTO(
            regon: $regon,
            nip: '1234567890',
            nipStatus: null,
            name: 'Test Company',
            shortName: null,
            registrationNumber: '',
            registrationDate: '',
            establishmentDate: '',
            businessStartDate: '',
            regonRegistrationDate: null,
            businessSuspensionDate: null,
            businessResumptionDate: null,
            lastChangeDate: '',
            businessEndDate: null,
            regonDeletionDate: null,
            bankruptcyDeclarationDate: null,
            bankruptcyEndDate: null,
            countryCode: 'PL',
            provinceCode: '',
            countyCode: '',
            municipalityCode: '',
            postalCode: '00-000',
            postalCityCode: '',
            cityCode: '',
            streetCode: '',
            buildingNumber: '',
            apartmentNumber: null,
            unusualLocation: null,
            phoneNumber: '',
            internalPhoneNumber: null,
            faxNumber: null,
            email: null,
            website: null,
            countryName: 'Polska',
            provinceName: '',
            countyName: '',
            municipalityName: '',
            cityName: '',
            postalCityName: '',
            streetName: '',
            legalFormCode: '',
            detailedLegalFormCode: '',
            financingFormCode: '',
            ownershipFormCode: '',
            foundingBodyCode: null,
            registrationAuthorityCode: '',
            registryTypeCode: '',
            legalFormName: '',
            detailedLegalFormName: '',
            financingFormName: '',
            ownershipFormName: '',
            foundingBodyName: null,
            registrationAuthorityName: '',
            registryTypeName: '',
            localUnitsCount: 0
        );

        $fullReportResponse = \Mockery::mock(Response::class);
        $fullReportResponse->shouldReceive('dto')->andReturn($fullReportResult);

        $this->apiConnector
            ->shouldReceive('send')
            ->with(\Mockery::type(GetFullReportRequest::class))
            ->andReturn($fullReportResponse)
        ;

        $result = $this->service->findByRegon($regon);

        $this->assertInstanceOf(RegonFullReportResultDTO::class, $result);
        $this->assertEquals($regon, $result->regon);
        $this->assertEquals('Test Company', $result->name);
    }

    public function testFindByNipThrowsExceptionForInvalidNip(): void
    {
        $this->expectException(RegonLookupException::class);
        $this->expectExceptionMessage('Invalid NIP format. NIP must be 10 digits.');

        $this->service->findByNip('123');
    }

    public function testFindByRegonThrowsExceptionForInvalidRegon(): void
    {
        $this->expectException(RegonLookupException::class);
        $this->expectExceptionMessage('Invalid REGON format. REGON must be 9 or 14 digits.');

        $this->service->findByRegon('123');
    }

    public function testFindByNipUsesCache(): void
    {
        $nip   = '1234567890';
        $regon = '123456789';

        // Mock search response
        $searchResult = new RegonLookupResultDTO(
            regon: $regon,
            nip: $nip,
            name: 'Test Company',
            shortName: null,
            registrationNumber: null,
            registrationDate: null,
            startDate: null,
            endDate: null,
            phoneNumber: null,
            email: null,
            website: null,
            address: null
        );

        $searchResponse = \Mockery::mock(Response::class);
        $searchResponse->shouldReceive('dto')->andReturn($searchResult);

        // Mock full report response
        $fullReportResult = new RegonFullReportResultDTO(
            regon: $regon,
            nip: $nip,
            nipStatus: null,
            name: 'Test Company',
            shortName: null,
            registrationNumber: '',
            registrationDate: '',
            establishmentDate: '',
            businessStartDate: '',
            regonRegistrationDate: null,
            businessSuspensionDate: null,
            businessResumptionDate: null,
            lastChangeDate: '',
            businessEndDate: null,
            regonDeletionDate: null,
            bankruptcyDeclarationDate: null,
            bankruptcyEndDate: null,
            countryCode: 'PL',
            provinceCode: '',
            countyCode: '',
            municipalityCode: '',
            postalCode: '00-000',
            postalCityCode: '',
            cityCode: '',
            streetCode: '',
            buildingNumber: '',
            apartmentNumber: null,
            unusualLocation: null,
            phoneNumber: '',
            internalPhoneNumber: null,
            faxNumber: null,
            email: null,
            website: null,
            countryName: 'Polska',
            provinceName: '',
            countyName: '',
            municipalityName: '',
            cityName: '',
            postalCityName: '',
            streetName: '',
            legalFormCode: '',
            detailedLegalFormCode: '',
            financingFormCode: '',
            ownershipFormCode: '',
            foundingBodyCode: null,
            registrationAuthorityCode: '',
            registryTypeCode: '',
            legalFormName: '',
            detailedLegalFormName: '',
            financingFormName: '',
            ownershipFormName: '',
            foundingBodyName: null,
            registrationAuthorityName: '',
            registryTypeName: '',
            localUnitsCount: 0
        );

        $fullReportResponse = \Mockery::mock(Response::class);
        $fullReportResponse->shouldReceive('dto')->andReturn($fullReportResult);

        $this->apiConnector
            ->shouldReceive('send')
            ->with(\Mockery::type(SearchRequest::class))
            ->andReturn($searchResponse)
            ->once()
        ;

        $this->apiConnector
            ->shouldReceive('send')
            ->with(\Mockery::type(GetFullReportRequest::class))
            ->andReturn($fullReportResponse)
            ->once()
        ;

        // First call should hit the API
        $result1 = $this->service->findByNip($nip);
        $this->assertInstanceOf(RegonFullReportResultDTO::class, $result1);

        // Second call should use cache
        $result2 = $this->service->findByNip($nip);
        $this->assertInstanceOf(RegonFullReportResultDTO::class, $result2);
        $this->assertEquals($result1, $result2);
    }

    public function testFindByRegonUsesCache(): void
    {
        $regon = '123456789';

        $fullReportResult = new RegonFullReportResultDTO(
            regon: $regon,
            nip: '1234567890',
            nipStatus: null,
            name: 'Test Company',
            shortName: null,
            registrationNumber: '',
            registrationDate: '',
            establishmentDate: '',
            businessStartDate: '',
            regonRegistrationDate: null,
            businessSuspensionDate: null,
            businessResumptionDate: null,
            lastChangeDate: '',
            businessEndDate: null,
            regonDeletionDate: null,
            bankruptcyDeclarationDate: null,
            bankruptcyEndDate: null,
            countryCode: 'PL',
            provinceCode: '',
            countyCode: '',
            municipalityCode: '',
            postalCode: '00-000',
            postalCityCode: '',
            cityCode: '',
            streetCode: '',
            buildingNumber: '',
            apartmentNumber: null,
            unusualLocation: null,
            phoneNumber: '',
            internalPhoneNumber: null,
            faxNumber: null,
            email: null,
            website: null,
            countryName: 'Polska',
            provinceName: '',
            countyName: '',
            municipalityName: '',
            cityName: '',
            postalCityName: '',
            streetName: '',
            legalFormCode: '',
            detailedLegalFormCode: '',
            financingFormCode: '',
            ownershipFormCode: '',
            foundingBodyCode: null,
            registrationAuthorityCode: '',
            registryTypeCode: '',
            legalFormName: '',
            detailedLegalFormName: '',
            financingFormName: '',
            ownershipFormName: '',
            foundingBodyName: null,
            registrationAuthorityName: '',
            registryTypeName: '',
            localUnitsCount: 0
        );

        $fullReportResponse = \Mockery::mock(Response::class);
        $fullReportResponse->shouldReceive('dto')->andReturn($fullReportResult);

        $this->apiConnector
            ->shouldReceive('send')
            ->with(\Mockery::type(GetFullReportRequest::class))
            ->andReturn($fullReportResponse)
            ->once()
        ;

        // First call should hit the API
        $result1 = $this->service->findByRegon($regon);
        $this->assertInstanceOf(RegonFullReportResultDTO::class, $result1);

        // Second call should use cache
        $result2 = $this->service->findByRegon($regon);
        $this->assertInstanceOf(RegonFullReportResultDTO::class, $result2);
        $this->assertEquals($result1, $result2);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
