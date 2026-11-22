<?php

namespace Database\Factories;

use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Section>
 */
class SectionFactory extends Factory
{
    protected $model = Section::class;

    public function definition(): array
    {
        return [
            'grade_level_id' => GradeLevel::factory(),
            'school_year_id' => SchoolYear::factory(),
            'name' => 'Section-'.fake()->unique()->lexify('???'),
            'adviser_teacher_id' => null,
        ];
    }
}
