<?php

namespace Tests\Feature\Domain\Skills;

use App\Domain\Auth\Models\User;
use App\Domain\Skills\Controllers\SkillController;
use App\Domain\Skills\Models\Skill;
use App\Domain\Skills\Models\SkillCategory;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use Tests\Traits\WithAuthenticatedUser;

/**
 * @internal
 */
#[CoversClass(SkillController::class)]
class SkillApiTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;

    private string $baseUrl = '/api/v1/skills';

    private SkillCategory $category;

    private User $user;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant   = Tenant::factory()->create();
        $this->user     = $this->authenticateUser($this->tenant);
        $this->category = SkillCategory::factory()->create();
    }

    public function testCanListSkills(): void
    {
        $skills = Skill::factory()
            ->count(3)
            ->create([
                'category' => $this->category->name,
            ])
        ;

        $response = $this->getJson($this->baseUrl);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'category',
                        'name',
                        'description',
                        'createdAt',
                        'updatedAt',
                    ],
                ],
                'meta',
            ])
            ->assertJsonCount(3, 'data')
        ;
    }

    public function testCanCreateSkill(): void
    {
        $skillData = [
            'category'    => $this->category->name,
            'name'        => 'Test Skill',
            'description' => 'Test Description',
        ];

        $response = $this->postJson($this->baseUrl, $skillData);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'category',
                    'name',
                    'description',
                    'createdAt',
                    'updatedAt',
                ],
            ])
            ->assertJson([
                'data' => [
                    'name'        => $skillData['name'],
                    'description' => $skillData['description'],
                ],
            ])
        ;

        $this->assertDatabaseHas('skills', [
            'category'    => $this->category->name,
            'name'        => $skillData['name'],
            'description' => $skillData['description'],
        ]);
    }

    public function testCannotCreateSkillWithInvalidData(): void
    {
        $skillData = [
            'category' => 'invalid-uuid',
            'name'     => '',
        ];

        $response = $this->postJson($this->baseUrl, $skillData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                'category',
                'name',
            ])
        ;
    }

    public function testCanShowSkill(): void
    {
        $skill = Skill::factory()->create([
            'category' => $this->category->name,
        ]);

        $response = $this->getJson($this->baseUrl . '/' . $skill->id);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'category',
                    'name',
                    'description',
                    'createdAt',
                    'updatedAt',
                ],
            ])
            ->assertJson([
                'data' => [
                    'id'          => $skill->id,
                    'name'        => $skill->name,
                    'description' => $skill->description,
                ],
            ])
        ;
    }

    public function testCanUpdateSkill(): void
    {
        $skill = Skill::factory()->create([
            'category' => $this->category->name,
        ]);

        $updateData = [
            'category'    => $this->category->name,
            'name'        => 'Updated Skill',
            'description' => 'Updated Description',
        ];

        $response = $this->putJson($this->baseUrl . '/' . $skill->id, $updateData);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'category',
                    'name',
                    'description',
                    'createdAt',
                    'updatedAt',
                ],
            ])
            ->assertJson([
                'data' => [
                    'name'        => $updateData['name'],
                    'description' => $updateData['description'],
                ],
            ])
        ;

        $this->assertDatabaseHas('skills', [
            'id'          => $skill->id,
            'name'        => $updateData['name'],
            'description' => $updateData['description'],
        ]);
    }

    public function testCanDeleteSkill(): void
    {
        $skill = Skill::factory()->create([
            'category' => $this->category->name,
        ]);

        $response = $this->deleteJson($this->baseUrl . '/' . $skill->id);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing('skills', ['id' => $skill->id]);
    }

    public function testReturns404ForNonexistentSkill(): void
    {
        $response = $this->getJson($this->baseUrl . '/nonexistent-id');

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
