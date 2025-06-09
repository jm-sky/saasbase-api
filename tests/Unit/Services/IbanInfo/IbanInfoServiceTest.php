<?php

namespace Tests\Unit\Services\IbanInfo;

use App\Domain\IbanInfo\Models\BankCode;
use App\Services\IbanApi\DTOs\BankDTO;
use App\Services\IbanApi\DTOs\IbanApiResponse;
use App\Services\IbanApi\DTOs\IbanDataDTO;
use App\Services\IbanApi\IbanApiService;
use App\Services\IbanInfo\IbanCacheService;
use App\Services\IbanInfo\IbanInfoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

/**
 * @internal
 */
#[CoversClass(IbanInfoService::class)]
class IbanInfoServiceTest extends TestCase
{
    use RefreshDatabase;
    use MockeryPHPUnitIntegration;

    protected function setUp(): void
    {
        $this->markTestSkipped('This test is not implemented');
    }

    public function testReturnsFromCacheIfAvailable(): void
    {
        $iban     = 'PL82105010121000009031264832';
        $bankCode = BankCode::factory()->make();

        $ibanApiService   = \Mockery::mock(IbanApiService::class);
        $ibanCacheService = \Mockery::mock(IbanCacheService::class);
        $ibanInfoService  = new IbanInfoService($ibanApiService, $ibanCacheService);

        $ibanCacheService
            ->shouldReceive('get')
            ->once()
            ->with('PL', '10501012')
            ->andReturn($bankCode)
        ;

        $result = $ibanInfoService->getBankInfoFromIban($iban);

        $this->assertEquals($bankCode->bank_name, $result->bankName);
    }

    public function testReturnsFromDbIfNotInCache(): void
    {
        $iban     = 'PL82105010121000009031264832';
        $bankCode = BankCode::factory()->create([
            'country_code' => 'PL',
            'bank_code'    => '10501012',
            'validated_at' => now(),
        ]);

        $ibanApiService   = \Mockery::mock(IbanApiService::class);
        $ibanCacheService = \Mockery::mock(IbanCacheService::class);
        $ibanInfoService  = new IbanInfoService($ibanApiService, $ibanCacheService);

        $ibanCacheService->shouldReceive('get')->once()->andReturn(null);
        $ibanCacheService->shouldReceive('put')->once()->with($bankCode);

        $result = $ibanInfoService->getBankInfoFromIban($iban);

        $this->assertEquals($bankCode->bank_name, $result->bankName);
    }

    public function testReturnsFromApiIfNotInCacheOrDb(): void
    {
        $iban        = 'DE89370400440532013000';
        $countryCode = 'DE';
        $bankCode    = '37040044';

        $ibanApiService   = \Mockery::mock(IbanApiService::class);
        $ibanCacheService = \Mockery::mock(IbanCacheService::class);
        $ibanInfoService  = new IbanInfoService($ibanApiService, $ibanCacheService);

        $ibanCacheService->shouldReceive('get')->once()->andReturn(null);
        $ibanCacheService->shouldReceive('put')->once();

        $apiResponse = new IbanApiResponse(
            200,
            'OK',
            [],
            0,
            new IbanDataDTO(
                $countryCode,
                null,
                null,
                'EUR',
                null,
                null,
                null,
                null,
                new BankDTO(
                    'Deutsche Bundesbank TEST DE',
                    'DEUTDEDB105x',
                    null,
                    null,
                    null
                )
            )
        );

        $ibanApiService->shouldReceive('getIbanInfo')->once()->with($iban)->andReturn($apiResponse);

        $result = $ibanInfoService->getBankInfoFromIban($iban);

        $this->assertEquals('Deutsche Bundesbank TEST DE', $result->bankName);
        $this->assertDatabaseHas('bank_codes', [
            'country_code' => $countryCode,
            'bank_code'    => $bankCode,
            'bank_name'    => 'Deutsche Bundesbank TEST DE',
        ]);
    }

    public function testRevalidatesStaleDbRecord(): void
    {
        $iban        = 'PL82105010121000009031264832';
        $countryCode = 'PL';
        $bankPart    = '10501012';

        BankCode::factory()->create([
            'country_code' => $countryCode,
            'bank_code'    => $bankPart,
            'bank_name'    => 'Old Bank Name',
            'validated_at' => now()->subDays(40),
        ]);

        $ibanApiService   = \Mockery::mock(IbanApiService::class);
        $ibanCacheService = \Mockery::mock(IbanCacheService::class);
        $ibanInfoService  = new IbanInfoService($ibanApiService, $ibanCacheService);

        $ibanCacheService->shouldReceive('get')->once()->andReturn(null);
        $ibanCacheService->shouldReceive('put')->once();

        $apiResponse = new IbanApiResponse(
            200,
            'OK',
            [],
            0,
            new IbanDataDTO(
                $countryCode,
                null,
                null,
                'PLN',
                null,
                null,
                null,
                null,
                new BankDTO(
                    'New Bank Name From API',
                    'INGBPLPWXXX',
                    null,
                    null,
                    null
                )
            )
        );

        $ibanApiService->shouldReceive('getIbanInfo')->once()->with($iban)->andReturn($apiResponse);

        $result = $ibanInfoService->getBankInfoFromIban($iban);

        $this->assertEquals('New Bank Name From API', $result->bankName);
        $this->assertDatabaseHas('bank_codes', [
            'bank_name' => 'New Bank Name From API',
        ]);
    }
}
