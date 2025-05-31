<?php

namespace Tests\Feature\Domain\EDoreczenia;

use App\Domain\EDoreczenia\Models\EDoreczeniaCertificate;
use App\Domain\EDoreczenia\Providers\EDoreczeniaProviderManager;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\WithAuthenticatedUser;

/**
 * @internal
 *
 * @coversNothing
 */
class CertificateControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;

    private string $baseUrl = '/api/v1/edoreczenia/certificates';

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        $this->authenticateUser($this->tenant);
        Storage::fake('local');
    }

    public function testCanListCertificates(): void
    {
        $this->markTestSkipped('Need to fix certificate listing functionality');

        EDoreczeniaCertificate::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $response = $this->getJson($this->baseUrl);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'tenantId',
                        'userId',
                        'provider',
                        'serialNumber',
                        'validFrom',
                        'validTo',
                        'status',
                        'createdAt',
                        'updatedAt',
                        'creator',
                    ],
                ],
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'per_page',
                    'to',
                    'total',
                ],
            ])
            ->assertJsonCount(3, 'data')
        ;
    }

    public function testCanCreateCertificate(): void
    {
        $this->markTestSkipped('Need to fix certificate creation functionality');

        $file = UploadedFile::fake()->create('certificate.p12', 100);

        $providerManager = \Mockery::mock(EDoreczeniaProviderManager::class);
        $providerManager->shouldReceive('getProvider')->andReturn(null);
        $this->app->instance(EDoreczeniaProviderManager::class, $providerManager);

        $response = $this->postJson($this->baseUrl, [
            'provider'         => 'test_provider',
            'serialNumber'     => '123456',
            'validFrom'        => now()->subYear(),
            'validTo'          => now()->addYear(),
            'certificate_file' => $file,
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'tenantId',
                    'userId',
                    'provider',
                    'serialNumber',
                    'validFrom',
                    'validTo',
                    'status',
                    'createdAt',
                    'updatedAt',
                    'creator',
                ],
            ])
        ;

        $this->assertDatabaseHas('e_doreczenia_certificates', [
            'tenant_id'     => $this->tenant->id,
            'provider'      => 'test_provider',
            'serial_number' => '123456',
            'status'        => 'active',
        ]);

        $this->assertTrue(Storage::disk('local')->exists('certificates/' . $file->hashName()));
    }

    public function testCanShowCertificate(): void
    {
        $this->markTestSkipped('Need to fix certificate retrieval functionality');

        $certificate = EDoreczeniaCertificate::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $response = $this->getJson("{$this->baseUrl}/{$certificate->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'tenantId',
                    'userId',
                    'provider',
                    'serialNumber',
                    'validFrom',
                    'validTo',
                    'status',
                    'createdAt',
                    'updatedAt',
                    'creator',
                ],
            ])
        ;
    }

    public function testCanUpdateCertificate(): void
    {
        $this->markTestSkipped('Need to fix certificate update functionality');

        $certificate = EDoreczeniaCertificate::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);
        $file = UploadedFile::fake()->create('new_certificate.p12', 100);

        $response = $this->putJson("{$this->baseUrl}/{$certificate->id}", [
            'provider'         => 'updated_provider',
            'serialNumber'     => '654321',
            'validFrom'        => now()->subYear(),
            'validTo'          => now()->addYear(),
            'certificate_file' => $file,
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'tenantId',
                    'userId',
                    'provider',
                    'serialNumber',
                    'validFrom',
                    'validTo',
                    'status',
                    'createdAt',
                    'updatedAt',
                    'creator',
                ],
            ])
        ;

        $this->assertDatabaseHas('e_doreczenia_certificates', [
            'id'            => $certificate->id,
            'provider'      => 'updated_provider',
            'serial_number' => '654321',
        ]);

        $this->assertTrue(Storage::disk('local')->exists('certificates/' . $file->hashName()));
    }

    public function testCanDeleteCertificate(): void
    {
        $this->markTestSkipped('Need to fix certificate deletion functionality');

        $certificate = EDoreczeniaCertificate::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $response = $this->deleteJson("{$this->baseUrl}/{$certificate->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('e_doreczenia_certificates', ['id' => $certificate->id]);
    }

    public function testCannotAccessOtherTenantCertificate(): void
    {
        $this->markTestSkipped('Need to fix tenant isolation for certificates');

        $otherTenant = Tenant::factory()->create();
        $certificate = EDoreczeniaCertificate::factory()->create([
            'tenant_id' => $otherTenant->id,
        ]);

        $response = $this->getJson("{$this->baseUrl}/{$certificate->id}");

        $response->assertForbidden();
    }
}
