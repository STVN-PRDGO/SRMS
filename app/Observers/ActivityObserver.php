<?php

namespace App\Observers;

use App\Jobs\RecalculateStudentGradeJob;
use App\Models\Activity;
use App\Models\Student;

class ActivityObserver
{
    public function saved(Activity $activity): void
    {
        $this->dispatch($activity);
    }

    public function deleted(Activity $activity): void
    {
        $this->dispatch($activity);
    }

    private function dispatch(Activity $activity): void
    {
        $sectionId = $activity->classSubject()->value('section_id');

        if ($sectionId === null) {
            return;
        }

        $studentIds = Student::query()->where('section_id', $sectionId)->pluck('id');

        foreach ($studentIds as $studentId) {
            RecalculateStudentGradeJob::dispatch(
                $studentId,
                $activity->class_subject_id,
                $activity->grading_period_id,
            )->afterCommit();
        }
    }
}
