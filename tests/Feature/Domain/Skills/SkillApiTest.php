<?php

namespace Tests\Feature\Domain\Skills;

use App\Domain\Auth\Models\User;
use App\Domain\Skills\Models\Skill;
use App\Domain\Skills\Models\SkillCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
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
                'category_id' => $this->category->id,
            ]);

        $response = $this->getJson($this->baseUrl);

        $response->assertStatus(200)
            ->assertJsonCount(3)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'categoryId',
                    'name',
                    'description',
                    'createdAt',
                    'updatedAt',
                    'deletedAt'
                ]
            ]);
    }

    public function test_can_create_skill(): void
    {
        $skillData = [
            'categoryId' => $this->category->id,
            'name' => 'Test Skill',
            'description' => 'Test Description',
        ];

        $response = $this->postJson($this->baseUrl, $skillData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'categoryId',
                'name',
                'description',
                'createdAt',
                'updatedAt',
                'deletedAt'
            ])
            ->assertJson([
                'name' => $skillData['name'],
                'description' => $skillData['description'],
            ]);

        $this->assertDatabaseHas('skills', [
            'category_id' => $this->category->id,
            'name' => $skillData['name'],
            'description' => $skillData['description'],
        ]);
    }

    public function test_cannot_create_skill_with_invalid_data(): void
    {
        $skillData = [
            'categoryId' => 'invalid-uuid',
            'name' => '',
        ];

        $response = $this->postJson($this->baseUrl, $skillData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'categoryId',
                'name',
            ]);
    }

    public function test_can_show_skill(): void
    {
        $skill = Skill::factory()->create([
            'category_id' => $this->category->id,
        ]);

        $response = $this->getJson($this->baseUrl . '/' . $skill->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'categoryId',
                'name',
                'description',
                'createdAt',
                'updatedAt',
                'deletedAt'
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
            'category_id' => $this->category->id,
        ]);

        $updateData = [
            'categoryId' => $this->category->id,
            'name' => 'Updated Skill',
            'description' => 'Updated Description',
        ];

        $response = $this->putJson($this->baseUrl . '/' . $skill->id, $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'categoryId',
                'name',
                'description',
                'createdAt',
                'updatedAt',
                'deletedAt'
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
            'category_id' => $this->category->id,
        ]);

        $response = $this->deleteJson($this->baseUrl . '/' . $skill->id);

        $response->assertStatus(204);
        $this->assertSoftDeleted('skills', ['id' => $skill->id]);
    }

    public function test_returns_404_for_nonexistent_skill(): void
    {
        $response = $this->getJson($this->baseUrl . '/nonexistent-id');

        $response->assertStatus(404);
    }
}
