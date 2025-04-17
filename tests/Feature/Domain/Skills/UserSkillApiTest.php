<?php

namespace Tests\Feature\Domain\Skills;

use App\Domain\Auth\Models\User;
use App\Domain\Skills\Models\Skill;
use App\Domain\Skills\Models\UserSkill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Illuminate\Http\Response;

class UserSkillApiTest extends TestCase
{
    use RefreshDatabase;

    private string $baseUrl = '/api/v1/user-skills';
    private User $user;
    private Skill $skill;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->skill = Skill::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_can_list_user_skills(): void
    {
        $userSkills = UserSkill::factory()
            ->count(3)
            ->create([
                'user_id' => $this->user->id,
                'skill_id' => $this->skill->id,
            ]);

        $response = $this->getJson($this->baseUrl);

        $response->assertStatus(200)
            ->assertJsonCount(3)
            ->assertJsonStructure([
                '*' => [
                    'userId',
                    'skillId',
                    'level',
                    'acquiredAt',
                    'createdAt',
                    'updatedAt',
                    'deletedAt'
                ]
            ]);
    }

    public function test_can_create_user_skill(): void
    {
        $userSkillData = [
            'userId' => $this->user->id,
            'skillId' => $this->skill->id,
            'level' => 3,
            'acquiredAt' => now()->format('Y-m-d'),
        ];

        $response = $this->postJson($this->baseUrl, $userSkillData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'userId',
                'skillId',
                'level',
                'acquiredAt',
                'createdAt',
                'updatedAt',
                'deletedAt'
            ])
            ->assertJson([
                'level' => $userSkillData['level'],
                'acquiredAt' => $userSkillData['acquiredAt'],
            ]);

        $this->assertDatabaseHas('user_skills', [
            'user_id' => $this->user->id,
            'skill_id' => $this->skill->id,
            'level' => $userSkillData['level'],
            'acquired_at' => $userSkillData['acquiredAt'],
        ]);
    }

    public function test_cannot_create_user_skill_with_invalid_data(): void
    {
        $userSkillData = [
            'userId' => 'invalid-uuid',
            'skillId' => 'invalid-uuid',
            'level' => 6,
            'acquiredAt' => 'invalid-date',
        ];

        $response = $this->postJson($this->baseUrl, $userSkillData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                'userId',
                'skillId',
                'level',
                'acquiredAt',
            ]);
    }

    public function test_can_show_user_skill(): void
    {
        $userSkill = UserSkill::factory()->create([
            'user_id' => $this->user->id,
            'skill_id' => $this->skill->id,
        ]);

        $response = $this->getJson($this->baseUrl . '/' . $userSkill->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'userId',
                'skillId',
                'level',
                'acquiredAt',
                'createdAt',
                'updatedAt',
                'deletedAt'
            ])
            ->assertJson([
                'userId' => $this->user->id,
                'skillId' => $this->skill->id,
                'level' => $userSkill->level,
            ]);
    }

    public function test_can_update_user_skill(): void
    {
        $userSkill = UserSkill::factory()->create([
            'user_id' => $this->user->id,
            'skill_id' => $this->skill->id,
        ]);

        $updateData = [
            'userId' => $this->user->id,
            'skillId' => $this->skill->id,
            'level' => 4,
            'acquiredAt' => now()->format('Y-m-d'),
        ];

        $response = $this->putJson($this->baseUrl . '/' . $userSkill->id, $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'userId',
                'skillId',
                'level',
                'acquiredAt',
                'createdAt',
                'updatedAt',
                'deletedAt'
            ])
            ->assertJson([
                'level' => $updateData['level'],
                'acquiredAt' => $updateData['acquiredAt'],
            ]);

        $this->assertDatabaseHas('user_skills', [
            'id' => $userSkill->id,
            'level' => $updateData['level'],
            'acquired_at' => $updateData['acquiredAt'],
        ]);
    }

    public function test_can_delete_user_skill(): void
    {
        $userSkill = UserSkill::factory()->create([
            'user_id' => $this->user->id,
            'skill_id' => $this->skill->id,
        ]);

        $response = $this->deleteJson($this->baseUrl . '/' . $userSkill->id);

        $response->assertStatus(204);
        $this->assertSoftDeleted('user_skills', ['id' => $userSkill->id]);
    }

    public function test_returns_404_for_nonexistent_user_skill(): void
    {
        $response = $this->getJson($this->baseUrl . '/nonexistent-id');

        $response->assertStatus(404);
    }
}
