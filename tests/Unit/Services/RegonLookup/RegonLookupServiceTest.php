<?php

namespace Tests\Unit\Services\RegonLookup;

use App\Services\RegonLookup\DTOs\RegonAuthResultDTO;
use App\Services\RegonLookup\DTOs\RegonLookupResultDTO;
use App\Services\RegonLookup\DTOs\RegonReportUnified;
use App\Services\RegonLookup\Exceptions\RegonLookupException;
use App\Services\RegonLookup\Integrations\RegonApiConnector;
use App\Services\RegonLookup\Integrations\Requests\GetFullReportRequest;
use App\Services\RegonLookup\Integrations\Requests\LoginRequest;
use App\Services\RegonLookup\Integrations\Requests\SearchRequest;
use App\Services\RegonLookup\Services\RegonLookupService;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\CoversClass;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Tests\TestCase;

/**
 * @internal
 */
#[CoversClass(RegonLookupService::class)]
class RegonLookupServiceTest extends TestCase
{
    private RegonApiConnector $apiConnector;

    private RegonLookupService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiConnector = new RegonApiConnector();
        $this->service      = new RegonLookupService($this->apiConnector);

        config()->set('regon_lookup.user_key', '1234567890');

        Cache::flush();
    }

    public function testFindByNipReturnsNullWhenSearchFails(): void
    {
        $nip = '1234567890';

        Saloon::fake([
            SearchRequest::class => MockResponse::make('', HttpResponse::HTTP_INTERNAL_SERVER_ERROR),
        ]);

        $result = $this->service->findByNip($nip);
        $this->assertNull($result);
    }

    public function testFindByNipReturnsNullWhenSearchResultIsInvalid(): void
    {
        $nip = '1234567890';

        Saloon::fake([
            SearchRequest::class => MockResponse::make('invalid-response', HttpResponse::HTTP_OK),
        ]);

        $result = $this->service->findByNip($nip);
        $this->assertNull($result);
    }

    public function testFindByNipReturnsFullReportWhenSearchSucceeds(): void
    {
        $this->markTestSkipped('Need to mock XML response');

        $nip   = '1234567890';
        $regon = '123456789';

        // Mock search response
        $searchResult = new RegonLookupResultDTO(
            name: 'Test Company',
            regon: $regon,
            nip: $nip,
            type: \App\Services\RegonLookup\Enums\EntityType::LegalPerson,
            statusNip: null,
            dateOfEnd: null,
            voivodeship: null,
            province: null,
            community: null,
            city: null,
            postalCode: null,
            street: null,
            building: null,
            flat: null,
            silosId: null
        );

        // Mock full report response
        $fullReportResult = new RegonReportUnified(
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
            registrationDeletionDate: null,
            registrationAuthorityCode: '',
            registryTypeCode: '',
            registrationAuthorityName: '',
            registryTypeName: '',
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
            localUnitsCount: 0,
            hasNotStartedActivity: null,
            cache: null
        );

        Saloon::fake([
            LoginRequest::class         => MockResponse::make(json_encode(new RegonAuthResultDTO('1234567890')), HttpResponse::HTTP_OK),
            SearchRequest::class        => MockResponse::make(json_encode($searchResult), HttpResponse::HTTP_OK),
            GetFullReportRequest::class => MockResponse::make(json_encode($fullReportResult), HttpResponse::HTTP_OK),
        ]);

        $result = $this->service->findByNip($nip, throw: true);

        $this->assertInstanceOf(RegonReportUnified::class, $result);
        $this->assertEquals($regon, $result->regon);
        $this->assertEquals($nip, $result->nip);
        $this->assertEquals('Test Company', $result->name);
    }

    public function testFindByRegonReturnsNullWhenRequestFails(): void
    {
        $regon = '123456789';

        Saloon::fake([
            GetFullReportRequest::class => MockResponse::make('', HttpResponse::HTTP_INTERNAL_SERVER_ERROR),
        ]);

        $result = $this->service->findByRegon($regon);
        $this->assertNull($result);
    }

    public function testFindByRegonReturnsFullReportWhenRequestSucceeds(): void
    {
        $this->markTestSkipped('Need to mock XML response');

        $regon = '123456789';

        $fullReportResult = new RegonReportUnified(
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
            registrationDeletionDate: null,
            registrationAuthorityCode: '',
            registryTypeCode: '',
            registrationAuthorityName: '',
            registryTypeName: '',
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
            localUnitsCount: 0,
            hasNotStartedActivity: null,
            cache: null
        );

        Saloon::fake([
            LoginRequest::class         => MockResponse::make(json_encode(new RegonAuthResultDTO('1234567890')), HttpResponse::HTTP_OK),
            GetFullReportRequest::class => MockResponse::make(json_encode($fullReportResult), HttpResponse::HTTP_OK),
        ]);

        $result = $this->service->findByRegon($regon);

        $this->assertInstanceOf(RegonReportUnified::class, $result);
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
        $this->markTestSkipped('Need to mock XML response');

        $nip   = '1234567890';
        $regon = '123456789';

        // Mock search response
        $searchResult = new RegonLookupResultDTO(
            name: 'Test Company',
            regon: $regon,
            nip: $nip,
            type: \App\Services\RegonLookup\Enums\EntityType::LegalPerson,
            statusNip: null,
            dateOfEnd: null,
            voivodeship: null,
            province: null,
            community: null,
            city: null,
            postalCode: null,
            street: null,
            building: null,
            flat: null,
            silosId: null
        );

        // Mock full report response
        $fullReportResult = new RegonReportUnified(
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
            registrationDeletionDate: null,
            registrationAuthorityCode: '',
            registryTypeCode: '',
            registrationAuthorityName: '',
            registryTypeName: '',
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
            localUnitsCount: 0,
            hasNotStartedActivity: null,
            cache: null
        );

        Saloon::fake([
            SearchRequest::class        => MockResponse::make(json_encode($searchResult), HttpResponse::HTTP_OK),
            GetFullReportRequest::class => MockResponse::make(json_encode($fullReportResult), HttpResponse::HTTP_OK),
        ]);

        // First call should hit the API
        $result1 = $this->service->findByNip($nip);
        $this->assertInstanceOf(RegonReportUnified::class, $result1);

        // Second call should use cache
        $result2 = $this->service->findByNip($nip);
        $this->assertInstanceOf(RegonReportUnified::class, $result2);
        $this->assertEquals($result1, $result2);
    }

    public function testFindByRegonUsesCache(): void
    {
        $this->markTestSkipped('Need to mock XML response');

        $regon = '123456789';

        $fullReportResult = new RegonReportUnified(
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
            registrationDeletionDate: null,
            registrationAuthorityCode: '',
            registryTypeCode: '',
            registrationAuthorityName: '',
            registryTypeName: '',
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
            localUnitsCount: 0,
            hasNotStartedActivity: null,
            cache: null
        );

        Saloon::fake([
            GetFullReportRequest::class => MockResponse::make(json_encode($fullReportResult), HttpResponse::HTTP_OK),
        ]);

        // First call should hit the API
        $result1 = $this->service->findByRegon($regon);
        $this->assertInstanceOf(RegonReportUnified::class, $result1);

        // Second call should use cache
        $result2 = $this->service->findByRegon($regon);
        $this->assertInstanceOf(RegonReportUnified::class, $result2);
        $this->assertEquals($result1, $result2);
    }
}
