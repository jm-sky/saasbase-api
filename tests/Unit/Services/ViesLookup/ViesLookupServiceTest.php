<?php

namespace Tests\Unit\Services\ViesLookup;

use App\Services\ViesLookup\DTOs\ViesLookupResultDTO;
use App\Services\ViesLookup\Exceptions\ViesLookupException;
use App\Services\ViesLookup\Integrations\Requests\CheckVatRequest;
use App\Services\ViesLookup\Integrations\ViesConnector;
use App\Services\ViesLookup\Services\ViesLookupService;
use Illuminate\Support\Facades\Cache;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class ViesLookupServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function testFindByVatReturnsNullWhenSearchFails(): void
    {
        Saloon::fake([
            CheckVatRequest::class => MockResponse::make('', HttpResponse::HTTP_BAD_REQUEST),
        ]);

        $connector = new ViesConnector();
        $service   = new ViesLookupService($connector);

        $this->expectException(ViesLookupException::class);
        $this->expectExceptionMessage('Unsuccessful VIES API response: 400');
        $service->findByVat('PL', '1111111111');
    }

    public function testFindByVatReturnsNullWhenSearchResultIsInvalid(): void
    {
        $invalidXml = '<?xml version="1.0" encoding="UTF-8"?><invalid>';

        Saloon::fake([
            CheckVatRequest::class => MockResponse::make($invalidXml, HttpResponse::HTTP_OK),
        ]);

        $connector = new ViesConnector();
        $service   = new ViesLookupService($connector);

        $this->expectException(ViesLookupException::class);
        $this->expectExceptionMessage('Invalid VIES XML response');
        $service->findByVat('PL', '2222222222');
    }

    public function testFindByVatReturnsFullReportWhenSearchSucceeds(): void
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
          <soap:Body>
            <checkVatResponse xmlns="urn:ec.europa.eu:taxud:vies:services:checkVat:types">
              <countryCode>PL</countryCode>
              <vatNumber>3333333333</vatNumber>
              <valid>true</valid>
              <name>Test Company</name>
              <address>Test Address</address>
            </checkVatResponse>
          </soap:Body>
        </soap:Envelope>';

        Saloon::fake([
            CheckVatRequest::class => MockResponse::make($xml, HttpResponse::HTTP_OK),
        ]);

        $connector = new ViesConnector();
        $service   = new ViesLookupService($connector);

        $result = $service->findByVat('PL', '3333333333');
        $this->assertInstanceOf(ViesLookupResultDTO::class, $result);
        $this->assertEquals('PL', $result->countryCode);
        $this->assertEquals('3333333333', $result->vatNumber);
        $this->assertTrue($result->valid);
        $this->assertEquals('Test Company', $result->name);
        $this->assertEquals('Test Address', $result->address);
    }

    public function testFindByVatUsesCache(): void
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
          <soap:Body>
            <checkVatResponse xmlns="urn:ec.europa.eu:taxud:vies:services:checkVat:types">
              <countryCode>PL</countryCode>
              <vatNumber>4444444444</vatNumber>
              <valid>true</valid>
              <name>Test Company</name>
              <address>Test Address</address>
            </checkVatResponse>
          </soap:Body>
        </soap:Envelope>';

        Saloon::fake([
            CheckVatRequest::class => MockResponse::make($xml, HttpResponse::HTTP_OK),
        ]);

        $connector = new ViesConnector();
        $service   = new ViesLookupService($connector);

        $result1 = $service->findByVat('PL', '4444444444');
        $result2 = $service->findByVat('PL', '4444444444');
        $this->assertEquals($result1, $result2);
    }

    public function testFindByVatHandlesSoapFault(): void
    {
        $soapFault = '<?xml version="1.0" encoding="UTF-8"?>
        <soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
          <soap:Body>
            <soap:Fault>
              <faultcode>soap:Client</faultcode>
              <faultstring>VIES API error: Invalid VAT number format</faultstring>
            </soap:Fault>
          </soap:Body>
        </soap:Envelope>';

        Saloon::fake([
            CheckVatRequest::class => MockResponse::make($soapFault, HttpResponse::HTTP_OK),
        ]);

        $connector = new ViesConnector();
        $service   = new ViesLookupService($connector);

        $this->expectException(ViesLookupException::class);
        $this->expectExceptionMessage('VIES API error: Invalid VAT number format');
        $service->findByVat('PL', '5555555555');
    }
}
