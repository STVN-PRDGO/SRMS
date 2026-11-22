<?php

namespace Database\Factories;

use App\Models\GradeLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GradeLevel>
 */
class GradeLevelFactory extends Factory
{
    protected $model = GradeLevel::class;

    public function definition(): array
    {
        $order = fake()->unique()->numberBetween(7, 12);

        return [
            'name' => "Grade {$order}",
            'order' => $order,
        ];
    }
}
