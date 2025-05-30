<?php

namespace Tests\Unit\Services\BankInfo;

use App\Domain\Bank\Models\Bank;
use App\Services\IbanApi\IbanApiService;
use App\Services\IbanApi\Integrations\Requests\ValidateIbanRequest;
use App\Services\IbanInfo\IbanInfoService;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\CoversClass;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Tests\TestCase;

/**
 * @internal
 *
 * TODO: Check polish and non-polish IBANs
 * TODO: Check if we can get bank info from IBANs from other countries
 *       - Germany: DE89 3704 0044 0532 0130 00
 *       - France: FR14 2004 1010 0505 0001 3M02 606
 *       - Poland: PL82105010121000009031264832
 */
#[CoversClass(IbanInfoService::class)]
class IbanInfoServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        IbanApiService::$throw = true;
    }

    public function testItCanGetBankInfoFromIban()
    {
        Bank::create([
            'bank_name'    => 'ING Bank Śląski SA',
            'branch_name'  => 'Oddział w Legionowie, ul. Handlowa 14',
            'routing_code' => '10501012',
            'bank_code'    => '1050',
            'swift'        => 'INGBPLPWXXX',
            'country'      => 'PL',
        ]);

        $service = new IbanInfoService();
        $result  = $service->getBankInfoFromIban('PL82105010121000009031264832');

        $this->assertEquals('ING Bank Śląski SA', $result->bankName);
        $this->assertEquals('Oddział w Legionowie, ul. Handlowa 14', $result->branchName);
        $this->assertEquals('10501012', $result->routingCode);
        $this->assertEquals('1050', $result->bankCode);
        $this->assertEquals('INGBPLPWXXX', $result->swift);
    }

    public function testItCanGetBankInfoFromIbanFromOtherCountry()
    {
        Saloon::fake([
            ValidateIbanRequest::class => MockResponse::make(json_encode([
                'result'      => HttpResponse::HTTP_OK,
                'message'     => 'OK',
                'validations' => [],
                'expremental' => 0,
                'data'        => [
                    'bank' => [
                        'bank_name' => 'Deutsche Bundesbank TEST DE',
                        'bic'       => 'DEUTDEDB105x',
                    ],
                ],
            ]), HttpResponse::HTTP_OK),
        ]);

        $service = new IbanInfoService();

        $result  = $service->getBankInfoFromIban('DE89370400440532013000');

        $this->assertEquals('Deutsche Bundesbank TEST DE', $result->bankName);
        $this->assertEquals('DEUTDEDB105x', $result->swift);
        $this->assertEquals('EUR', $result->currency);
    }
}
