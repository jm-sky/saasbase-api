<?php

namespace Tests\Feature\Domain\Utils;

use App\Domain\Utils\Controllers\IbanInfoController;
use App\Services\IbanApi\Integrations\Requests\ValidateIbanRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\CoversClass;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use Tests\Traits\WithAuthenticatedUser;

/**
 * @internal
 */
#[CoversClass(IbanInfoController::class)]
class IbanInfoControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;

    private string $baseUrl = '/api/v1/utils/iban-info';

    protected function setUp(): void
    {
        parent::setUp();
        $this->authenticateUser();
        Cache::flush();
    }

    public function testSuccessfulLookupReturnsEnrichedData(): void
    {
        $iban        = 'DE89370400440532013000';
        $apiResponse = [
            'result'      => 200,
            'message'     => 'OK',
            'validations' => [],
            'expremental' => 0,
            'data'        => [
                'country_code'  => 'DE',
                'currency_code' => 'EUR',
                'bank'          => [
                    'bank_name' => 'Deutsche Bundesbank',
                    'bic'       => 'MARKDEFF',
                ],
            ],
        ];

        Saloon::fake([
            ValidateIbanRequest::class => MockResponse::make(json_encode($apiResponse), Response::HTTP_OK),
        ]);

        $response = $this->getJson("{$this->baseUrl}?iban={$iban}");

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    'iban'             => 'DE89370400440532013000',
                    'bankName'         => 'Deutsche Bundesbank',
                    'swift'            => 'MARKDEFF',
                    'currency'         => 'EUR',
                    'validationStatus' => 'UNKNOWN',
                    'cacheStatus'      => 'UNKNOWN',
                ],
            ])
        ;
    }

    public function testLookupForUnknownIbanReturnsNotFound(): void
    {
        $iban = 'XX123456789';

        Saloon::fake([
            ValidateIbanRequest::class => MockResponse::make(json_encode(['result' => 404, 'message' => 'Not Found']), Response::HTTP_NOT_FOUND),
        ]);

        $response = $this->getJson("{$this->baseUrl}?iban={$iban}");

        $response->assertOk()
            ->assertJson([
                'error' => 'Bank not found for the provided IBAN',
            ])
        ;
    }

    public function testLookupWithInvalidIbanFormatReturnsValidationError(): void
    {
        $iban = 'INVALID_IBAN';

        $response = $this->getJson("{$this->baseUrl}?iban={$iban}");

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('iban')
        ;
    }
}
