<?php

namespace Tests\Feature\Domain\Skills;

use App\Domain\Auth\Models\User;
use App\Domain\Skills\Controllers\UserSkillController;
use App\Domain\Skills\Models\Skill;
use App\Domain\Skills\Models\UserSkill;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;
use Tests\Traits\WithAuthenticatedUser;

/**
 * @internal
 */
#[CoversClass(UserSkillController::class)]
class UserSkillApiTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;

    private string $baseUrl = '/api/v1/user/skills';

    private User $user;

    private Skill $skill;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        $this->skill  = Skill::factory()->create();
        $this->user   = $this->authenticateUser($this->tenant);
    }

    public function testCanListUserSkills(): void
    {
        UserSkill::factory()
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
                        'id',
                        'userId',
                        'skillId',
                        'name',
                        'category',
                        'description',
                        'level',
                        'acquiredAt',
                        'createdAt',
                        'updatedAt',
                    ],
                ],
            ])
        ;
    }

    public function testCanCreateUserSkill(): void
    {
        $userSkillData = [
            'skillId'    => $this->skill->id,
            'level'      => 3,
            'acquiredAt' => now()->format('Y-m-d'),
        ];

        $response = $this->postJson($this->baseUrl, $userSkillData);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'userId',
                    'skillId',
                    'name',
                    'category',
                    'description',
                    'level',
                    'acquiredAt',
                    'createdAt',
                    'updatedAt',
                ],
            ])
            ->assertJson([
                'data' => [
                    'userId'     => $this->user->id,
                    'skillId'    => $this->skill->id,
                    'level'      => $userSkillData['level'],
                    'acquiredAt' => $userSkillData['acquiredAt'],
                ],
            ])
        ;

        $this->assertDatabaseHas('user_skill', [
            'user_id'     => $this->user->id,
            'skill_id'    => $this->skill->id,
            'level'       => $userSkillData['level'],
            'acquired_at' => $userSkillData['acquiredAt'],
        ]);
    }

    public function testCannotCreateUserSkillWithInvalidData(): void
    {
        $userSkillData = [
            'skillId'    => 'invalid-uuid',
            'level'      => 6,
            'acquiredAt' => 'invalid-date',
        ];

        $response = $this->postJson($this->baseUrl, $userSkillData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
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
                    'id',
                    'userId',
                    'skillId',
                    'name',
                    'category',
                    'description',
                    'level',
                    'acquiredAt',
                    'createdAt',
                    'updatedAt',
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
            'level'      => 4,
            'acquiredAt' => now()->format('Y-m-d'),
        ];

        $response = $this->putJson($this->baseUrl . '/' . $userSkill->id, $updateData);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'userId',
                    'skillId',
                    'name',
                    'category',
                    'description',
                    'level',
                    'acquiredAt',
                    'createdAt',
                    'updatedAt',
                ],
            ])
            ->assertJson([
                'data' => [
                    'level'      => $updateData['level'],
                    'acquiredAt' => $updateData['acquiredAt'],
                ],
            ])
        ;

        $this->assertDatabaseHas('user_skill', [
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
        $this->assertDatabaseMissing('user_skill', ['id' => $userSkill->id]);
    }

    public function testReturns404ForNonexistentUserSkill(): void
    {
        $response = $this->getJson($this->baseUrl . '/00000000-0000-0000-0000-000000000000');

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
