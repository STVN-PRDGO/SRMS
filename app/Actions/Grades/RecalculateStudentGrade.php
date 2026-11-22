<?php

namespace App\Actions\Grades;

use App\Models\ClassSubject;
use App\Models\Grade;
use App\Models\GradingPeriod;
use App\Models\Student;
use App\Services\GradeCalculator;
use App\Services\PerformanceEvaluator;

class RecalculateStudentGrade
{
    public function __construct(
        private readonly GradeCalculator $calculator,
        private readonly PerformanceEvaluator $evaluator,
    ) {}

    public function __invoke(int $studentId, int $classSubjectId, int $gradingPeriodId): void
    {
        $period = GradingPeriod::query()->find($gradingPeriodId);
        $student = Student::query()->find($studentId);
        $classSubject = ClassSubject::query()->find($classSubjectId);

        if ($period === null || $student === null || $classSubject === null || $period->isClosed()) {
            return;
        }

        $existing = Grade::query()
            ->where('student_id', $student->id)
            ->where('class_subject_id', $classSubject->id)
            ->where('grading_period_id', $period->id)
            ->first();

        if ($existing?->is_locked) {
            return;
        }

        $result = $this->calculator->calculate($student, $classSubject, $period);

        Grade::query()->updateOrCreate(
            [
                'student_id' => $student->id,
                'class_subject_id' => $classSubject->id,
                'grading_period_id' => $period->id,
            ],
            [
                'raw_grade' => $result['raw_grade'],
                'letter' => $result['letter'],
                'remark' => $result['remark'],
                'is_locked' => false,
                'computed_at' => now(),
            ],
        );

        $this->evaluator->evaluate($student, $period);
    }
}
