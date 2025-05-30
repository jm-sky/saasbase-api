<?php

namespace Tests\Unit\Services\BankInfo;

use App\Domain\Bank\Models\Bank;
use App\Services\IbanInfo\IbanInfoService;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\CoversClass;
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
        $this->assertNotNull($result);
    }

    public function testItCanGetBankInfoFromIbanFromOtherCountry()
    {
        $this->markTestIncomplete('TODO: Check polish and non-polish IBANs');

        $service = new IbanInfoService();
        $result  = $service->getBankInfoFromIban('DE89370400440532013000');
        $this->assertNotNull($result);
    }
}
