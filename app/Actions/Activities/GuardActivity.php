<?php

namespace App\Actions\Activities;

use App\Models\Activity;
use Illuminate\Validation\ValidationException;

class GuardActivity
{
    public function __invoke(Activity $activity): void
    {
        if ((float) $activity->max_score <= 0) {
            throw ValidationException::withMessages([
                'max_score' => 'The maximum score must be greater than zero.',
            ]);
        }

        $activity->loadMissing('gradingPeriod');

        if ($activity->gradingPeriod !== null && $activity->gradingPeriod->isClosed()) {
            throw ValidationException::withMessages([
                'grading_period_id' => 'This grading period is closed.',
            ]);
        }
    }
}
