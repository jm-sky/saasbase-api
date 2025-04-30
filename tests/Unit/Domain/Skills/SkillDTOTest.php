<?php

namespace Tests\Unit\Domain\Skills;

use App\Domain\Skills\DTOs\SkillCategoryDTO;
use App\Domain\Skills\DTOs\SkillDTO;
use App\Domain\Skills\Models\Skill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversNothing;
use Tests\TestCase;

/**
 * @internal
 */
#[CoversNothing]
class SkillDTOTest extends TestCase
{
    use RefreshDatabase;

    public function testCanCreateSkillDtoFromModel(): void
    {
        $skill = Skill::factory()->create();
        $dto   = SkillDTO::fromModel($skill);

        $this->assertEquals($skill->id, $dto->id);
        $this->assertEquals($skill->category, $dto->category);
        $this->assertEquals($skill->name, $dto->name);
        $this->assertEquals($skill->description, $dto->description);
        $this->assertEquals($skill->created_at, $dto->createdAt);
        $this->assertEquals($skill->updated_at, $dto->updatedAt);
        $this->assertNull($dto->skillCategory);
    }

    public function testCanCreateSkillDtoWithSkillCategory(): void
    {
        $skill = Skill::factory()->create();
        $skill->load('skillCategory');

        $dto = SkillDTO::fromModel($skill, true);

        $this->assertInstanceOf(SkillCategoryDTO::class, $dto->skillCategory);
        $this->assertEquals($skill->skillCategory->id, $dto->skillCategory->id);
        $this->assertEquals($skill->skillCategory->name, $dto->skillCategory->name);
    }

    public function testCanConvertSkillDtoToArray(): void
    {
        $skill = Skill::factory()->create();
        $dto   = SkillDTO::fromModel($skill);
        $array = $dto->toArray();

        $this->assertIsArray($array);
        $this->assertEquals($skill->id, $array['id']);
        $this->assertEquals($skill->category, $array['category']);
        $this->assertEquals($skill->name, $array['name']);
        $this->assertEquals($skill->description, $array['description']);
        $this->assertEquals($skill->created_at?->toIso8601String(), $array['createdAt']);
        $this->assertEquals($skill->updated_at?->toIso8601String(), $array['updatedAt']);
        $this->assertNull($array['skill_category']);
    }

    public function testCanConvertSkillDtoWithSkillCategoryToArray(): void
    {
        $skill = Skill::factory()->create();
        $skill->load('skillCategory');

        $dto   = SkillDTO::fromModel($skill, true);
        $array = $dto->toArray();

        $this->assertIsArray($array['skill_category']);
        $this->assertEquals($skill->skillCategory->id, $array['skill_category']['id']);
        $this->assertEquals($skill->skillCategory->name, $array['skill_category']['name']);
    }
}
