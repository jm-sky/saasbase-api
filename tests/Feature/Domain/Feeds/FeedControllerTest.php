<?php

namespace Tests\Feature\Domain\Feeds;

use App\Domain\Feeds\Models\Feed;
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
class FeedControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;

    private string $baseUrl = '/api/v1/feeds';

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        $this->authenticateUser($this->tenant);
        Storage::fake('local');
    }

    public function testCanListFeeds(): void
    {
        $this->markTestSkipped('Need to fix feed listing functionality');

        Tenant::bypassTenant($this->tenant->id, function () {
            Feed::factory()->count(3)->create([
                'tenant_id' => $this->tenant->id,
            ]);
        });

        $response = $this->getJson($this->baseUrl);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'tenantId',
                        'userId',
                        'title',
                        'content',
                        'commentsCount',
                        'createdAt',
                        'updatedAt',
                        'creator' => [
                            'id',
                            'name',
                            'email',
                        ],
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

    public function testCanCreateFeed(): void
    {
        $this->markTestSkipped('Need to fix feed creation functionality');

        $file = UploadedFile::fake()->create('image.jpg', 100);

        $response = $this->postJson($this->baseUrl, [
            'title'       => 'Test Feed',
            'content'     => 'Test Content',
            'attachments' => [$file],
        ]);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'tenantId',
                    'userId',
                    'title',
                    'content',
                    'commentsCount',
                    'createdAt',
                    'updatedAt',
                    'creator' => [
                        'id',
                        'name',
                        'email',
                    ],
                ],
            ])
        ;

        Tenant::bypassTenant($this->tenant->id, function () {
            $this->assertDatabaseHas('feeds', [
                'tenant_id' => $this->tenant->id,
                'title'     => 'Test Feed',
                'content'   => 'Test Content',
            ]);
        });

        $this->assertTrue(Storage::disk('local')->exists('feeds/attachments/' . $file->hashName()));
    }

    public function testCanShowFeed(): void
    {
        $this->markTestSkipped('Need to fix feed retrieval functionality');

        $feed = null;
        Tenant::bypassTenant($this->tenant->id, function () use (&$feed) {
            $feed = Feed::factory()->create([
                'tenant_id' => $this->tenant->id,
            ]);
        });

        $response = $this->getJson($this->baseUrl . '/' . $feed->id);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'tenantId',
                    'userId',
                    'title',
                    'content',
                    'commentsCount',
                    'createdAt',
                    'updatedAt',
                    'creator' => [
                        'id',
                        'name',
                        'email',
                    ],
                ],
            ])
            ->assertJsonPath('data.id', $feed->id)
        ;
    }

    public function testCanDeleteFeed(): void
    {
        $this->markTestSkipped('Need to fix feed deletion functionality');

        $feed = null;
        Tenant::bypassTenant($this->tenant->id, function () use (&$feed) {
            $feed = Feed::factory()->create([
                'tenant_id' => $this->tenant->id,
            ]);
        });

        $response = $this->deleteJson($this->baseUrl . '/' . $feed->id);

        $response->assertNoContent();

        Tenant::bypassTenant($this->tenant->id, function () use ($feed) {
            $this->assertDatabaseMissing('feeds', ['id' => $feed->id]);
        });
    }

    public function testCannotAccessOtherTenantFeed(): void
    {
        $this->markTestSkipped('Need to fix tenant isolation for feeds');

        $otherTenant = Tenant::factory()->create();
        $feed        = null;

        Tenant::bypassTenant($otherTenant->id, function () use ($otherTenant, &$feed) {
            $feed = Feed::factory()->create([
                'tenant_id' => $otherTenant->id,
            ]);
        });

        $response = $this->getJson($this->baseUrl . '/' . $feed->id);

        $response->assertForbidden();
    }
}
