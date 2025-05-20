<?php

namespace Tests\Feature\Domain\Skills;

use App\Domain\Auth\Models\User;
use App\Domain\Skills\Models\Skill;
use App\Domain\Skills\Models\UserSkill;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\CoversNothing;
use Tests\TestCase;
use Tests\Traits\WithAuthenticatedUser;

/**
 * @internal
 */
#[CoversNothing]
class UserSkillApiTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;

    private string $baseUrl = '/api/v1/user-skills';

    private User $user;

    private Skill $skill;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        $this->user   = $this->authenticateUser($this->tenant);
        $this->skill  = Skill::factory()->create();
    }

    public function testCanListUserSkills(): void
    {
        $userSkills = UserSkill::factory()
            ->count(3)
            ->create([
                'user_id'  => $this->user->id,
                'skill_id' => $this->skill->id,
            ])
        ;

        $response = $this->getJson($this->baseUrl);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'userId',
                        'skillId',
                        'level',
                        'acquiredAt',
                        'createdAt',
                        'updatedAt',
                        'deletedAt',
                    ],
                ],
            ])
        ;
    }

    public function testCanCreateUserSkill(): void
    {
        $userSkillData = [
            'userId'     => $this->user->id,
            'skillId'    => $this->skill->id,
            'level'      => 3,
            'acquiredAt' => now()->format('Y-m-d'),
        ];

        $response = $this->postJson($this->baseUrl, $userSkillData);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'data' => [
                    'userId',
                    'skillId',
                    'level',
                    'acquiredAt',
                    'createdAt',
                    'updatedAt',
                    'deletedAt',
                ],
            ])
            ->assertJson([
                'data' => [
                    'level'      => $userSkillData['level'],
                    'acquiredAt' => $userSkillData['acquiredAt'],
                ],
            ])
        ;

        $this->assertDatabaseHas('user_skills', [
            'user_id'     => $this->user->id,
            'skill_id'    => $this->skill->id,
            'level'       => $userSkillData['level'],
            'acquired_at' => $userSkillData['acquiredAt'],
        ]);
    }

    public function testCannotCreateUserSkillWithInvalidData(): void
    {
        $userSkillData = [
            'userId'     => 'invalid-uuid',
            'skillId'    => 'invalid-uuid',
            'level'      => 6,
            'acquiredAt' => 'invalid-date',
        ];

        $response = $this->postJson($this->baseUrl, $userSkillData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                'userId',
                'skillId',
                'level',
                'acquiredAt',
            ])
        ;
    }

    public function testCanShowUserSkill(): void
    {
        $userSkill = UserSkill::factory()->create([
            'user_id'  => $this->user->id,
            'skill_id' => $this->skill->id,
        ]);

        $response = $this->getJson($this->baseUrl . '/' . $userSkill->id);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    'userId',
                    'skillId',
                    'level',
                    'acquiredAt',
                    'createdAt',
                    'updatedAt',
                    'deletedAt',
                ],
            ])
            ->assertJson([
                'data' => [
                    'userId'  => $this->user->id,
                    'skillId' => $this->skill->id,
                    'level'   => $userSkill->level,
                ],
            ])
        ;
    }

    public function testCanUpdateUserSkill(): void
    {
        $userSkill = UserSkill::factory()->create([
            'user_id'  => $this->user->id,
            'skill_id' => $this->skill->id,
        ]);

        $updateData = [
            'userId'     => $this->user->id,
            'skillId'    => $this->skill->id,
            'level'      => 4,
            'acquiredAt' => now()->format('Y-m-d'),
        ];

        $response = $this->putJson($this->baseUrl . '/' . $userSkill->id, $updateData);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'userId',
                    'skillId',
                    'level',
                    'acquiredAt',
                    'createdAt',
                    'updatedAt',
                    'deletedAt',
                ],
            ])
            ->assertJson([
                'data' => [
                    'level'      => $updateData['level'],
                    'acquiredAt' => $updateData['acquiredAt'],
                ],
            ])
        ;

        $this->assertDatabaseHas('user_skills', [
            'id'          => $userSkill->id,
            'level'       => $updateData['level'],
            'acquired_at' => $updateData['acquiredAt'],
        ]);
    }

    public function testCanDeleteUserSkill(): void
    {
        $userSkill = UserSkill::factory()->create([
            'user_id'  => $this->user->id,
            'skill_id' => $this->skill->id,
        ]);

        $response = $this->deleteJson($this->baseUrl . '/' . $userSkill->id);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertSoftDeleted('user_skills', ['id' => $userSkill->id]);
    }

    public function testReturns404ForNonexistentUserSkill(): void
    {
        $response = $this->getJson($this->baseUrl . '/nonexistent-id');

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
