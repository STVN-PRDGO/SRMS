<?php

namespace Database\Factories;

use App\Models\SchoolYear;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SchoolYear>
 */
class SchoolYearFactory extends Factory
{
    protected $model = SchoolYear::class;

    public function definition(): array
    {
        $start = fake()->unique()->numberBetween(2025, 2035);

        return [
            'name' => "{$start}-".($start + 1),
            'starts_on' => "{$start}-06-01",
            'ends_on' => ($start + 1).'-03-31',
            'is_current' => false,
        ];
    }
}
