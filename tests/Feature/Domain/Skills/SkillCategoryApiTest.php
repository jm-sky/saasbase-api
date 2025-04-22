<?php

namespace Tests\Feature\Domain\Skills;

use App\Domain\Auth\Models\User;
use App\Domain\Skills\Models\SkillCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\CoversNothing;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class SkillCategoryApiTest extends TestCase
{
    use RefreshDatabase;

    private string $baseUrl = '/api/v1/skill-categories';

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        self::markTestSkipped('This test class is temporarily disabled.');
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function testCanListCategories(): void
    {
        $categories = SkillCategory::factory()->count(3)->create();

        $response = $this->getJson($this->baseUrl);

        $response->assertStatus(200)
            ->assertJsonCount(3)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'description',
                    'createdAt',
                    'updatedAt',
                    'deletedAt',
                ],
            ])
        ;
    }

    public function testCanCreateCategory(): void
    {
        $categoryData = [
            'name'        => 'Test Category',
            'description' => 'Test Description',
        ];

        $response = $this->postJson($this->baseUrl, $categoryData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'name',
                'description',
                'createdAt',
                'updatedAt',
                'deletedAt',
            ])
            ->assertJson([
                'name'        => $categoryData['name'],
                'description' => $categoryData['description'],
            ])
        ;

        $this->assertDatabaseHas('skill_categories', [
            'name'        => $categoryData['name'],
            'description' => $categoryData['description'],
        ]);
    }

    public function testCannotCreateCategoryWithInvalidData(): void
    {
        $categoryData = [
            'name' => '',
        ];

        $response = $this->postJson($this->baseUrl, $categoryData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
        ;
    }

    public function testCanShowCategory(): void
    {
        $category = SkillCategory::factory()->create();

        $response = $this->getJson($this->baseUrl . '/' . $category->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'description',
                'createdAt',
                'updatedAt',
                'deletedAt',
            ])
            ->assertJson([
                'id'          => $category->id,
                'name'        => $category->name,
                'description' => $category->description,
            ])
        ;
    }

    public function testCanUpdateCategory(): void
    {
        $category = SkillCategory::factory()->create();

        $updateData = [
            'name'        => 'Updated Category',
            'description' => 'Updated Description',
        ];

        $response = $this->putJson($this->baseUrl . '/' . $category->id, $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'description',
                'createdAt',
                'updatedAt',
                'deletedAt',
            ])
            ->assertJson([
                'name'        => $updateData['name'],
                'description' => $updateData['description'],
            ])
        ;

        $this->assertDatabaseHas('skill_categories', [
            'id'          => $category->id,
            'name'        => $updateData['name'],
            'description' => $updateData['description'],
        ]);
    }

    public function testCanDeleteCategory(): void
    {
        $category = SkillCategory::factory()->create();

        $response = $this->deleteJson($this->baseUrl . '/' . $category->id);

        $response->assertStatus(204);
        $this->assertSoftDeleted('skill_categories', ['id' => $category->id]);
    }

    public function testReturns404ForNonexistentCategory(): void
    {
        $response = $this->getJson($this->baseUrl . '/nonexistent-id');

        $response->assertStatus(404);
    }
}
