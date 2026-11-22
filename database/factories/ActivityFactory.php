<?php

namespace Database\Factories;

use App\Enums\ActivityType;
use App\Models\Activity;
use App\Models\ClassSubject;
use App\Models\GradingComponent;
use App\Models\GradingPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
{
    protected $model = Activity::class;

    public function definition(): array
    {
        return [
            'class_subject_id' => ClassSubject::factory(),
            'grading_period_id' => GradingPeriod::factory(),
            'grading_component_id' => GradingComponent::factory(),
            'title' => fake()->words(3, true),
            'type' => fake()->randomElement(ActivityType::cases()),
            'description' => fake()->sentence(),
            'max_score' => 50.00,
            'due_date' => now()->addDays(7),
        ];
    }
}
