<?php

namespace Database\Factories;

use App\Enums\ScoreStatus;
use App\Models\Activity;
use App\Models\Score;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Score>
 */
class ScoreFactory extends Factory
{
    protected $model = Score::class;

    public function definition(): array
    {
        return [
            'activity_id' => Activity::factory(),
            'student_id' => Student::factory(),
            'score' => 45.00,
            'status' => ScoreStatus::Graded,
            'remarks' => null,
            'graded_by' => null,
        ];
    }
}
