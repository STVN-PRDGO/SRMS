<?php

namespace App\Actions\Periods;

use App\Actions\Grades\RecalculateStudentGrade;
use App\Enums\PeriodStatus;
use App\Models\Activity;
use App\Models\Grade;
use App\Models\GradingComponent;
use App\Models\GradingPeriod;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClosePeriod
{
    public function __construct(private readonly RecalculateStudentGrade $recalculate) {}

    public function __invoke(GradingPeriod $period, User $user): void
    {
        if (! $user->can('close_grading_period')) {
            throw ValidationException::withMessages([
                'period' => 'You cannot close this grading period.',
            ]);
        }

        if ($period->status !== PeriodStatus::Open) {
            throw ValidationException::withMessages([
                'period' => 'Only an open grading period can be closed.',
            ]);
        }

        $this->assertWeights($period);

        DB::transaction(function () use ($period): void {
            $activities = Activity::query()
                ->where('grading_period_id', $period->id)
                ->with('classSubject')
                ->get();

            $sectionIds = $activities->pluck('classSubject.section_id')->filter()->unique();
            $students = Student::query()->whereIn('section_id', $sectionIds)->get();

            foreach ($activities as $activity) {
                foreach ($students->where('section_id', $activity->classSubject->section_id) as $student) {
                    ($this->recalculate)($student->id, $activity->class_subject_id, $period->id);
                }
            }

            $period->update(['status' => PeriodStatus::Closed]);
            Grade::query()->where('grading_period_id', $period->id)->update(['is_locked' => true]);
        });
    }

    private function assertWeights(GradingPeriod $period): void
    {
        $subjectIds = Activity::query()
            ->where('grading_period_id', $period->id)
            ->with('classSubject')
            ->get()
            ->pluck('classSubject.subject_id')
            ->filter()
            ->unique();

        foreach ($subjectIds as $subjectId) {
            $weights = GradingComponent::query()->where('subject_id', $subjectId)->pluck('weight');

            if ($weights->isEmpty()) {
                $weights = GradingComponent::query()->whereNull('subject_id')->pluck('weight');
            }

            if (round((float) $weights->sum(), 2) !== 100.0) {
                throw ValidationException::withMessages([
                    'period' => 'Grading component weights must total 100 before this period can close.',
                ]);
            }
        }
    }
}
