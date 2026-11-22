<?php

namespace App\Actions\Periods;

use App\Actions\Grades\RecalculateStudentGrade;
use App\Enums\PeriodStatus;
use App\Models\Activity;
use App\Models\Grade;
use App\Models\GradingPeriod;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReopenPeriod
{
    public function __construct(private readonly RecalculateStudentGrade $recalculate) {}

    public function __invoke(GradingPeriod $period, User $user): void
    {
        if (! $user->can('reopen_grading_period')) {
            throw ValidationException::withMessages([
                'period' => 'You cannot reopen this grading period.',
            ]);
        }

        if (! $period->isClosed()) {
            throw ValidationException::withMessages([
                'period' => 'Only a closed grading period can be reopened.',
            ]);
        }

        DB::transaction(function () use ($period): void {
            $period->update(['status' => PeriodStatus::Open]);
            Grade::query()->where('grading_period_id', $period->id)->update(['is_locked' => false]);

            $activities = Activity::query()->where('grading_period_id', $period->id)->with('classSubject')->get();
            $students = Student::query()
                ->whereIn('section_id', $activities->pluck('classSubject.section_id')->filter()->unique())
                ->get();

            foreach ($activities as $activity) {
                foreach ($students->where('section_id', $activity->classSubject->section_id) as $student) {
                    ($this->recalculate)($student->id, $activity->class_subject_id, $period->id);
                }
            }
        });
    }
}
