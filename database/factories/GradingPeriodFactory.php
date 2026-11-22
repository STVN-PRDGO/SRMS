<?php

namespace Database\Factories;

use App\Enums\PeriodStatus;
use App\Models\GradingPeriod;
use App\Models\SchoolYear;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GradingPeriod>
 */
class GradingPeriodFactory extends Factory
{
    protected $model = GradingPeriod::class;

    public function definition(): array
    {
        return [
            'school_year_id' => SchoolYear::factory(),
            'name' => fake()->randomElement(['Q1', 'Q2', 'Q3', 'Q4']),
            'starts_on' => '2026-06-01',
            'ends_on' => '2026-08-15',
            'status' => PeriodStatus::Open,
        ];
    }
}
