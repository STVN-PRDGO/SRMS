<?php

namespace Database\Factories;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Teacher>
 */
class TeacherFactory extends Factory
{
    protected $model = Teacher::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'employee_no' => 'EMP-'.fake()->unique()->numerify('#####'),
            'department' => fake()->randomElement(['Mathematics', 'Science', 'English', 'Social Studies', 'IT', 'Filipino']),
            'contact_no' => fake()->phoneNumber(),
        ];
    }
}
