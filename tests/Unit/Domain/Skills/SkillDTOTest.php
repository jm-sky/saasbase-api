<?php

namespace Tests\Unit\Domain\Skills;

use App\Domain\Skills\DTOs\SkillDTO;
use App\Domain\Skills\Models\Skill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

/**
 * @internal
 */
#[CoversClass(SkillDTO::class)]
class SkillDTOTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_skill_dto_from_model(): void
    {
        $skill = Skill::factory()->create();
        $dto = SkillDTO::fromModel($skill);

        $this->assertEquals($skill->id, $dto->id);
        $this->assertEquals($skill->category, $dto->category);
        $this->assertEquals($skill->name, $dto->name);
        $this->assertEquals($skill->description, $dto->description);
        $this->assertEquals($skill->created_at, $dto->createdAt);
        $this->assertEquals($skill->updated_at, $dto->updatedAt);
    }

    public function test_can_convert_skill_dto_to_array(): void
    {
        $skill = Skill::factory()->create();
        $dto = SkillDTO::fromModel($skill);
        $array = $dto->toArray();

        $this->assertIsArray($array);
        $this->assertEquals($skill->id, $array['id']);
        $this->assertEquals($skill->category, $array['category']);
        $this->assertEquals($skill->name, $array['name']);
        $this->assertEquals($skill->description, $array['description']);
        $this->assertEquals($skill->created_at?->toIso8601String(), $array['createdAt']);
        $this->assertEquals($skill->updated_at?->toIso8601String(), $array['updatedAt']);
    }
}
