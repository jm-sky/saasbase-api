<?php

namespace Tests\Unit\Domain\Skills;

use App\Domain\Skills\DTOs\SkillDTO;
use App\Domain\Skills\DTOs\SkillCategoryDTO;
use App\Domain\Skills\Models\Skill;
use Tests\TestCase;

class SkillDTOTest extends TestCase
{
    public function test_can_create_skill_dto_from_model(): void
    {
        $skill = Skill::factory()->create();
        $dto = SkillDTO::fromModel($skill);

        $this->assertEquals($skill->id, $dto->id);
        $this->assertEquals($skill->category_id, $dto->category_id);
        $this->assertEquals($skill->name, $dto->name);
        $this->assertEquals($skill->description, $dto->description);
        $this->assertEquals($skill->created_at, $dto->created_at);
        $this->assertEquals($skill->updated_at, $dto->updated_at);
        $this->assertEquals($skill->deleted_at, $dto->deleted_at);
        $this->assertNull($dto->category);
    }

    public function test_can_create_skill_dto_with_category(): void
    {
        $skill = Skill::factory()->create();
        $skill->load('category');

        $dto = SkillDTO::fromModel($skill, true);

        $this->assertInstanceOf(SkillCategoryDTO::class, $dto->category);
        $this->assertEquals($skill->category->id, $dto->category->id);
        $this->assertEquals($skill->category->name, $dto->category->name);
    }

    public function test_can_convert_skill_dto_to_array(): void
    {
        $skill = Skill::factory()->create();
        $dto = SkillDTO::fromModel($skill);
        $array = $dto->toArray();

        $this->assertIsArray($array);
        $this->assertEquals($skill->id, $array['id']);
        $this->assertEquals($skill->category_id, $array['category_id']);
        $this->assertEquals($skill->name, $array['name']);
        $this->assertEquals($skill->description, $array['description']);
        $this->assertEquals($skill->created_at, $array['created_at']);
        $this->assertEquals($skill->updated_at, $array['updated_at']);
        $this->assertEquals($skill->deleted_at, $array['deleted_at']);
        $this->assertNull($array['category']);
    }

    public function test_can_convert_skill_dto_with_category_to_array(): void
    {
        $skill = Skill::factory()->create();
        $skill->load('category');

        $dto = SkillDTO::fromModel($skill, true);
        $array = $dto->toArray();

        $this->assertIsArray($array['category']);
        $this->assertEquals($skill->category->id, $array['category']['id']);
        $this->assertEquals($skill->category->name, $array['category']['name']);
    }
}
