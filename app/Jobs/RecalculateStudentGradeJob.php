<?php

namespace App\Jobs;

use App\Actions\Grades\RecalculateStudentGrade;
use App\Models\ClassSubject;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

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

        $teacherUserId = ClassSubject::query()->where('id', $this->classSubjectId)->with('teacher')->first()?->teacher?->user_id;
        if ($teacherUserId) {
            Cache::forget("dash.teacher.{$teacherUserId}");
        }
    }
}
