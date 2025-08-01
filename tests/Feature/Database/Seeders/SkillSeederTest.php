<?php

namespace Tests\Feature\Database\Seeders;

use App\Domain\Skills\Models\Skill;
use App\Domain\Skills\Models\SkillCategory;
use Database\Seeders\SkillCategorySeeder;
use Database\Seeders\SkillSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

/**
 * @internal
 */
#[CoversClass(SkillSeeder::class)]
class SkillSeederTest extends TestCase
{
    use RefreshDatabase;

    public function testSkillCategorySeederCreatesExpectedRecords(): void
    {
        $this->seed(SkillCategorySeeder::class);

        $this->assertDatabaseCount('skill_categories', 8);
        $this->assertDatabaseHas('skill_categories', [
            'name'        => 'Programming Languages',
            'description' => 'Programming and scripting languages',
        ]);
        $this->assertDatabaseHas('skill_categories', [
            'name'        => 'Web Development',
            'description' => 'Web development technologies and frameworks',
        ]);
    }

    public function testSkillSeederCreatesExpectedRecords(): void
    {
        // First seed categories
        $this->seed(SkillCategorySeeder::class);
        // Then seed skills
        $this->seed(SkillSeeder::class);

        $this->assertDatabaseCount('skills', 23); // Total number of skills

        // Test some skills from different categories
        $this->assertDatabaseHas('skills', [
            'category'    => 'Programming Languages',
            'name'        => 'PHP',
            'description' => 'PHP programming language',
        ]);

        $this->assertDatabaseHas('skills', [
            'category'    => 'Web Development',
            'name'        => 'Laravel',
            'description' => 'PHP web application framework',
        ]);

        $this->assertDatabaseHas('skills', [
            'category'    => 'Database',
            'name'        => 'MySQL',
            'description' => 'Open-source relational database management system',
        ]);
    }

    public function testSkillsAreProperlyRelatedToCategories(): void
    {
        $this->seed(SkillCategorySeeder::class);
        $this->seed(SkillSeeder::class);

        // Get Programming Languages category and check its skills
        $programmingCategory = SkillCategory::where('name', 'Programming Languages')->first();
        $this->assertNotNull($programmingCategory);

        // Get skills for this category
        $skills = Skill::where('category', 'Programming Languages')->get();
        $this->assertTrue($skills->contains('name', 'PHP'));
        $this->assertTrue($skills->contains('name', 'JavaScript'));

        // Get Web Development category and check its skills
        $webDevCategory = SkillCategory::where('name', 'Web Development')->first();
        $this->assertNotNull($webDevCategory);

        // Get skills for this category
        $skills = Skill::where('category', 'Web Development')->get();
        $this->assertTrue($skills->contains('name', 'Laravel'));
        $this->assertTrue($skills->contains('name', 'React'));
    }
}
