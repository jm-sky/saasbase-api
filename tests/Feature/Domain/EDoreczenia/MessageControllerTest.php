<?php

namespace Tests\Feature\Domain\EDoreczenia;

use App\Domain\EDoreczenia\Models\EDoreczeniaMessage;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use Tests\Traits\WithAuthenticatedUser;

/**
 * @internal
 */
#[CoversNothing]
class MessageControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;

    private string $baseUrl = '/api/v1/edoreczenia/messages';

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        $this->authenticateUser($this->tenant);
        Storage::fake('local');
    }

    public function testCanListMessages(): void
    {
        $this->markTestSkipped('Need to fix message listing functionality');

        EDoreczeniaMessage::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $response = $this->getJson($this->baseUrl);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'tenantId',
                        'userId',
                        'provider',
                        'recipient',
                        'subject',
                        'content',
                        'status',
                        'externalId',
                        'createdAt',
                        'updatedAt',
                    ],
                ],
                'meta' => [
                    'currentPage',
                    'lastPage',
                    'perPage',
                    'total',
                ],
            ])
        ;
    }

    public function testCanFilterMessagesByStatus(): void
    {
        $this->markTestSkipped('Need to fix message filtering functionality');

        EDoreczeniaMessage::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status'    => 'pending',
        ]);
        EDoreczeniaMessage::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status'    => 'sent',
        ]);

        $response = $this->getJson($this->baseUrl . '?filter[status]=pending');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'pending')
        ;
    }

    public function testCanCreateMessage(): void
    {
        $this->markTestSkipped('Need to fix message creation functionality');

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->postJson($this->baseUrl, [
            'provider'    => 'test',
            'recipient'   => 'test@example.com',
            'subject'     => 'Test Subject',
            'content'     => 'Test Content',
            'attachments' => [$file],
        ]);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'tenantId',
                    'userId',
                    'provider',
                    'recipient',
                    'subject',
                    'content',
                    'status',
                    'createdAt',
                    'updatedAt',
                ],
            ])
        ;

        $this->assertDatabaseHas('edoreczenia_messages', [
            'tenant_id' => $this->tenant->id,
            'provider'  => 'test',
            'recipient' => 'test@example.com',
            'subject'   => 'Test Subject',
            'content'   => 'Test Content',
            'status'    => 'pending',
        ]);

        $this->assertTrue(Storage::disk('local')->exists('edoreczenia/attachments/' . $file->hashName()));
    }

    public function testCanShowMessage(): void
    {
        $this->markTestSkipped('Need to fix message retrieval functionality');

        $message = EDoreczeniaMessage::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $response = $this->getJson($this->baseUrl . '/' . $message->id);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'tenantId',
                    'userId',
                    'provider',
                    'recipient',
                    'subject',
                    'content',
                    'status',
                    'createdAt',
                    'updatedAt',
                ],
            ])
            ->assertJsonPath('data.id', $message->id)
        ;
    }

    public function testCanUpdateMessage(): void
    {
        $this->markTestSkipped('Need to fix message update functionality');

        $message = EDoreczeniaMessage::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->putJson($this->baseUrl . '/' . $message->id, [
            'subject'     => 'Updated Subject',
            'content'     => 'Updated Content',
            'attachments' => [$file],
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'tenantId',
                    'userId',
                    'provider',
                    'recipient',
                    'subject',
                    'content',
                    'status',
                    'createdAt',
                    'updatedAt',
                ],
            ])
            ->assertJsonPath('data.subject', 'Updated Subject')
            ->assertJsonPath('data.content', 'Updated Content')
        ;

        $this->assertTrue(Storage::disk('local')->exists('edoreczenia/attachments/' . $file->hashName()));
    }

    public function testCanDeleteMessage(): void
    {
        $this->markTestSkipped('Need to fix message deletion functionality');

        $message = EDoreczeniaMessage::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $response = $this->deleteJson($this->baseUrl . '/' . $message->id);

        $response->assertNoContent();
        $this->assertDatabaseMissing('edoreczenia_messages', ['id' => $message->id]);
    }
}
