<?php

namespace Database\Factories;

use App\Models\GradingComponent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GradingComponent>
 */
class GradingComponentFactory extends Factory
{
    protected $model = GradingComponent::class;

    public function definition(): array
    {
        return [
            'subject_id' => null,
            'name' => fake()->randomElement(['Quiz', 'Activity', 'Exam', 'Project']),
            'weight' => 25.00,
        ];
    }
}
