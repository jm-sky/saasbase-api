<?php

namespace Tests\Unit\Domain\Skills;

use App\Domain\Skills\DTOs\SkillCategoryDTO;
use App\Domain\Skills\Models\SkillCategory;
use PHPUnit\Framework\Attributes\CoversNothing;
use Tests\TestCase;

/**
 * @internal
 */
#[CoversNothing]
class SkillCategoryDTOTest extends TestCase
{
    public function testCanCreateSkillCategoryDtoFromModel(): void
    {
        $category = SkillCategory::factory()->create();
        $dto      = SkillCategoryDTO::fromModel($category);

        $this->assertEquals($category->id, $dto->id);
        $this->assertEquals($category->name, $dto->name);
        $this->assertEquals($category->description, $dto->description);
        $this->assertEquals($category->created_at, $dto->createdAt);
        $this->assertEquals($category->updated_at, $dto->updatedAt);
        $this->assertEquals($category->deleted_at, $dto->deletedAt);
    }

    public function testCanConvertSkillCategoryDtoToArray(): void
    {
        $category = SkillCategory::factory()->create();
        $dto      = SkillCategoryDTO::fromModel($category);
        $array    = $dto->toArray();

        $this->assertIsArray($array);
        $this->assertEquals($category->id, $array['id']);
        $this->assertEquals($category->name, $array['name']);
        $this->assertEquals($category->description, $array['description']);
        $this->assertEquals($category->created_at?->toIso8601String(), $array['createdAt']);
        $this->assertEquals($category->updated_at?->toIso8601String(), $array['updatedAt']);
        $this->assertEquals($category->deleted_at?->toIso8601String(), $array['deletedAt']);
    }
}
