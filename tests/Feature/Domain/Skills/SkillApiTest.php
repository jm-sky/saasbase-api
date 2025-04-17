<?php

namespace Tests\Feature\Domain\Skills;

use App\Domain\Auth\Models\User;
use App\Domain\Skills\Models\Skill;
use App\Domain\Skills\Models\SkillCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class SkillApiTest extends TestCase
{
    use RefreshDatabase;

    private string $baseUrl = '/api/v1/skills';
    private SkillCategory $category;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->category = SkillCategory::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_can_list_skills(): void
    {
        $skills = Skill::factory()
            ->count(3)
            ->create([
                'category' => $this->category->name,
            ]);

        $response = $this->getJson($this->baseUrl);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'current_page',
                'data' => [
                    '*' => [
                        'id',
                        'category',
                        'name',
                        'description',
                        'createdAt',
                        'updatedAt'
                    ]
                ],
                'from',
                'links' => [],
                'per_page',
                'to',
                'total'
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_skill(): void
    {
        $skillData = [
            'category' => $this->category->name,
            'name' => 'Test Skill',
            'description' => 'Test Description',
        ];

        $response = $this->postJson($this->baseUrl, $skillData);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'id',
                'category',
                'name',
                'description',
                'createdAt',
                'updatedAt'
            ])
            ->assertJson([
                'name' => $skillData['name'],
                'description' => $skillData['description'],
            ]);

        $this->assertDatabaseHas('skills', [
            'category' => $this->category->name,
            'name' => $skillData['name'],
            'description' => $skillData['description'],
        ]);
    }

    public function test_cannot_create_skill_with_invalid_data(): void
    {
        $skillData = [
            'category' => 'invalid-uuid',
            'name' => '',
        ];

        $response = $this->postJson($this->baseUrl, $skillData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                'category',
                'name',
            ]);
    }

    public function test_can_show_skill(): void
    {
        $skill = Skill::factory()->create([
            'category' => $this->category->name,
        ]);

        $response = $this->getJson($this->baseUrl . '/' . $skill->id);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'id',
                'category',
                'name',
                'description',
                'createdAt',
                'updatedAt'
            ])
            ->assertJson([
                'id' => $skill->id,
                'name' => $skill->name,
                'description' => $skill->description,
            ]);
    }

    public function test_can_update_skill(): void
    {
        $skill = Skill::factory()->create([
            'category' => $this->category->name,
        ]);

        $updateData = [
            'category' => $this->category->name,
            'name' => 'Updated Skill',
            'description' => 'Updated Description',
        ];

        $response = $this->putJson($this->baseUrl . '/' . $skill->id, $updateData);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'id',
                'category',
                'name',
                'description',
                'createdAt',
                'updatedAt'
            ])
            ->assertJson([
                'name' => $updateData['name'],
                'description' => $updateData['description'],
            ]);

        $this->assertDatabaseHas('skills', [
            'id' => $skill->id,
            'name' => $updateData['name'],
            'description' => $updateData['description'],
        ]);
    }

    public function test_can_delete_skill(): void
    {
        $skill = Skill::factory()->create([
            'category' => $this->category->name,
        ]);

        $response = $this->deleteJson($this->baseUrl . '/' . $skill->id);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing('skills', ['id' => $skill->id]);
    }

    public function test_returns_404_for_nonexistent_skill(): void
    {
        $response = $this->getJson($this->baseUrl . '/nonexistent-id');

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
