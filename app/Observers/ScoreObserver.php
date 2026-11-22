<?php

namespace App\Observers;

use App\Jobs\RecalculateStudentGradeJob;
use App\Models\Score;

class ScoreObserver
{
    public function saved(Score $score): void
    {
        $this->dispatch($score);
    }

    public function deleted(Score $score): void
    {
        $this->dispatch($score);
    }

    private function dispatch(Score $score): void
    {
        $activity = $score->activity()->first();

        if ($activity === null) {
            return;
        }

        RecalculateStudentGradeJob::dispatch(
            $score->student_id,
            $activity->class_subject_id,
            $activity->grading_period_id,
        )->afterCommit();
    }
}
