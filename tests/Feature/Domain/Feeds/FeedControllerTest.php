<?php

namespace Tests\Feature\Domain\Feeds;

use App\Domain\Auth\Models\User;
use App\Domain\Feeds\Controllers\FeedController;
use App\Domain\Feeds\Models\Feed;
use App\Domain\Tenant\Models\Tenant;
use App\Services\ProfanityFilterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use Tests\Traits\WithAuthenticatedUser;
use Tests\Traits\WithFakeStorage;

/**
 * @internal
 */
#[CoversClass(FeedController::class)]
class FeedControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;
    use WithFakeStorage;

    private string $baseUrl = '/api/v1/feeds';

    private User $user;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->tenant = Tenant::factory()->create();
        $this->authenticateUser($this->tenant, $this->user);
        Storage::fake('local');

        // Mock ProfanityFilterService for all tests
        $profanityService = \Mockery::mock(ProfanityFilterService::class);
        $profanityService->shouldReceive('hasProfanity')->andReturn(false);
        // @phpstan-ignore-next-line
        $profanityService->shouldReceive('filterText')->andReturnUsing(function ($text) {
            return $text;
        });
        $this->app->instance(ProfanityFilterService::class, $profanityService);
    }

    public function test_can_list_feeds(): void
    {
        Tenant::bypassTenant($this->tenant->id, function () {
            Feed::factory()->count(3)->create([
                'tenant_id' => $this->tenant->id,
                'user_id' => $this->user->id,
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
            ]);
    }

    public function test_can_create_feed_without_attachment(): void
    {
        $response = $this->postJson($this->baseUrl, [
            'title' => 'Test Feed',
            'content' => 'Test Content',
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
            ]);

        Tenant::bypassTenant($this->tenant->id, function () {
            $this->assertDatabaseHas('feeds', [
                'tenant_id' => $this->tenant->id,
                'title' => 'Test Feed',
                'content' => 'Test Content',
            ]);
        });
    }

    public function test_can_create_feed_with_attachment(): void
    {
        $this->fakeStorage('media');

        $file = UploadedFile::fake()->image('image.jpg', 500, 500)->size(512);

        $response = $this->postJson($this->baseUrl, [
            'title' => 'Test Feed',
            'content' => 'Test Content',
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
            ]);

        Tenant::bypassTenant($this->tenant->id, function () {
            $this->assertDatabaseHas('feeds', [
                'tenant_id' => $this->tenant->id,
                'title' => 'Test Feed',
                'content' => 'Test Content',
            ]);

            $feed = Feed::first();

            $this->assertFileExists($feed->getFirstMedia('attachments')->getPath());
        });
    }

    public function test_can_show_feed(): void
    {
        $feed = Tenant::bypassTenant($this->tenant->id, function () {
            return Feed::factory()->create([
                'tenant_id' => $this->tenant->id,
                'user_id' => $this->user->id,
            ]);
        });

        $response = $this->getJson($this->baseUrl.'/'.$feed->id);

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
            ->assertJsonPath('data.id', $feed->id);
    }

    public function test_can_delete_feed(): void
    {
        $feed = Tenant::bypassTenant($this->tenant->id, function () {
            return Feed::factory()->create([
                'tenant_id' => $this->tenant->id,
                'user_id' => $this->user->id,
            ]);
        });

        $response = $this->deleteJson($this->baseUrl.'/'.$feed->id);

        $response->assertNoContent();

        Tenant::bypassTenant($this->tenant->id, function () use ($feed) {
            $this->assertDatabaseMissing('feeds', ['id' => $feed->id]);
        });
    }

    public function test_cannot_access_other_tenant_feed(): void
    {
        $otherTenant = Tenant::factory()->create();

        $feed = Tenant::bypassTenant($otherTenant->id, function () use ($otherTenant) {
            return Feed::factory()->create([
                'tenant_id' => $otherTenant->id,
                'user_id' => $this->user->id,
            ]);
        });

        $response = $this->getJson($this->baseUrl.'/'.$feed->id);

        $response->assertNotFound();
    }

    public function test_profanity_is_filtered_and_flagged(): void
    {
        // Override the default mock for this specific test
        $profanityService = \Mockery::mock(ProfanityFilterService::class);
        $profanityService->shouldReceive('hasProfanity')
            // @phpstan-ignore-next-line once() is a Mockery\Expectation method, not visible to PHPStan on the shouldReceive() return type
            ->once()
            ->andReturn(true);

        $this->app->instance(ProfanityFilterService::class, $profanityService);

        $file = UploadedFile::fake()->create('image.jpg', 100);

        $response = $this->postJson($this->baseUrl, [
            'title' => 'Test Feed',
            'content' => 'This is a test with some bad words: fuck shit',
            'attachments' => [$file],
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'content',
                ],
            ])
            ->assertJsonValidationErrors(['content']);

        Tenant::bypassTenant($this->tenant->id, function () {
            $this->assertDatabaseMissing('feeds', [
                'tenant_id' => $this->tenant->id,
                'title' => 'Test Feed',
                'content' => 'This is a test with some bad words: fuck shit',
            ]);
        });
    }
}
