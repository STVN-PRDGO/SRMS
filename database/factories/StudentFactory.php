<?php

namespace Database\Factories;

use App\Enums\StudentStatus;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'student_no' => 'STD-'.fake()->unique()->numerify('#####'),
            'section_id' => Section::factory(),
            'birthdate' => fake()->date('Y-m-d', '-13 years'),
            'guardian_name' => fake()->name(),
            'guardian_contact' => fake()->phoneNumber(),
            'status' => StudentStatus::Active,
        ];
    }
}
