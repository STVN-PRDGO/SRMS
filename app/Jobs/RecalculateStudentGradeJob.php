<?php

namespace App\Jobs;

use App\Actions\Grades\RecalculateStudentGrade;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RecalculateStudentGradeJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $studentId,
        public int $classSubjectId,
        public int $gradingPeriodId,
    ) {}

    public function handle(RecalculateStudentGrade $action): void
    {
        $action($this->studentId, $this->classSubjectId, $this->gradingPeriodId);
    }
}
