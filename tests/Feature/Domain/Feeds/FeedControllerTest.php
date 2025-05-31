<?php

namespace Tests\Feature\Domain\Feeds;

use App\Domain\Auth\Models\User;
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

    private User $user;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user   = User::factory()->create();
        $this->tenant = Tenant::factory()->create();
        $this->authenticateUser($this->tenant, $this->user);
        Storage::fake('local');
    }

    public function testCanListFeeds(): void
    {
        Tenant::bypassTenant($this->tenant->id, function () {
            Feed::factory()->count(3)->create([
                'tenant_id' => $this->tenant->id,
                'user_id'   => $this->user->id,
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
        \Mockery::mock(Feed::class)->shouldReceive('addMedia')->andReturnSelf();

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
        $feed = Tenant::bypassTenant($this->tenant->id, function () {
            return Feed::factory()->create([
                'tenant_id' => $this->tenant->id,
                'user_id'   => $this->user->id,
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
        $feed = Tenant::bypassTenant($this->tenant->id, function () {
            return Feed::factory()->create([
                'tenant_id' => $this->tenant->id,
                'user_id'   => $this->user->id,
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
        $otherTenant = Tenant::factory()->create();

        $feed = Tenant::bypassTenant($otherTenant->id, function () use ($otherTenant) {
            return Feed::factory()->create([
                'tenant_id' => $otherTenant->id,
                'user_id'   => $this->user->id,
            ]);
        });

        $response = $this->getJson($this->baseUrl . '/' . $feed->id);

        $response->assertNotFound();
    }
}
