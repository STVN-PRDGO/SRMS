<?php

namespace Database\Factories;

use App\Models\ClassSubject;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClassSubject>
 */
class ClassSubjectFactory extends Factory
{
    protected $model = ClassSubject::class;

    public function definition(): array
    {
        return [
            'section_id' => Section::factory(),
            'subject_id' => Subject::factory(),
            'teacher_id' => Teacher::factory(),
            'schedule' => ['room' => 'Rm 101', 'days' => ['Mon', 'Wed', 'Fri'], 'time' => '08:00 - 09:30'],
        ];
    }
}
