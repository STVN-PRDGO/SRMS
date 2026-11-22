<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Enums\ScoreStatus;
use App\Enums\Trend;
use App\Models\Activity;
use App\Models\Attendance;
use App\Models\ClassSubject;
use App\Models\Grade;
use App\Models\GradingPeriod;
use App\Models\PerformanceLevel;
use App\Models\PerformanceSummary;
use App\Models\Student;
use Illuminate\Support\Collection;

class PerformanceEvaluator
{
    public function evaluate(Student $student, GradingPeriod $period): void
    {
        if ($period->isClosed()) {
            return;
        }

        $grades = Grade::query()
            ->where('student_id', $student->id)
            ->where('grading_period_id', $period->id)
            ->whereNotNull('raw_grade')
            ->pluck('raw_grade');

        $average = $grades->isEmpty()
            ? null
            : round((float) $grades->avg(), 2, PHP_ROUND_HALF_UP);

        $attendanceRate = $this->attendanceRate($student, $period);
        $completionRate = $this->completionRate($student, $period);
        $level = $average === null ? null : $this->levelFor($average, $attendanceRate);

        PerformanceSummary::query()->updateOrCreate(
            [
                'student_id' => $student->id,
                'grading_period_id' => $period->id,
            ],
            [
                'general_average' => $average,
                'attendance_rate' => $attendanceRate,
                'activity_completion_rate' => $completionRate,
                'performance_level_id' => $level?->id,
                'trend' => $this->trend($student, $period, $average),
                'computed_at' => now(),
            ],
        );
    }

    private function attendanceRate(Student $student, GradingPeriod $period): ?float
    {
        $rows = Attendance::query()
            ->where('student_id', $student->id)
            ->whereDate('date', '>=', $period->starts_on)
            ->whereDate('date', '<=', $period->ends_on)
            ->get();

        $counted = $rows->whereIn('status', [
            AttendanceStatus::Present,
            AttendanceStatus::Late,
            AttendanceStatus::Absent,
        ]);

        if ($counted->isEmpty()) {
            return null;
        }

        $present = $counted->whereIn('status', [AttendanceStatus::Present, AttendanceStatus::Late])->count();

        return round(($present / $counted->count()) * 100, 2, PHP_ROUND_HALF_UP);
    }

    private function completionRate(Student $student, GradingPeriod $period): ?float
    {
        $classSubjectIds = ClassSubject::query()
            ->where('section_id', $student->section_id)
            ->pluck('id');

        $activities = Activity::query()
            ->whereIn('class_subject_id', $classSubjectIds)
            ->where('grading_period_id', $period->id)
            ->with(['scores' => fn ($query) => $query->where('student_id', $student->id)])
            ->get();

        if ($activities->isEmpty()) {
            return null;
        }

        $done = $activities->filter(function ($activity): bool {
            $score = $activity->scores->first();

            return $score !== null && in_array($score->status, [ScoreStatus::Graded, ScoreStatus::Excused], true);
        })->count();

        return round(($done / $activities->count()) * 100, 2, PHP_ROUND_HALF_UP);
    }

    private function levelFor(float $average, ?float $attendanceRate): ?PerformanceLevel
    {
        /** @var Collection<int, PerformanceLevel> $levels */
        $levels = cache()->rememberForever('performance_levels.all', fn () => PerformanceLevel::query()->orderBy('order')->get());

        if ($levels->isEmpty()) {
            return null;
        }

        foreach ($levels as $level) {
            $gradeMatches = $average >= (float) $level->min_grade && $average <= (float) $level->max_grade;
            $attendanceMatches = $level->min_attendance === null
                || $attendanceRate === null
                || $attendanceRate >= (float) $level->min_attendance;

            if ($gradeMatches && $attendanceMatches) {
                return $level;
            }
        }

        foreach ($levels as $level) {
            $gradeQualifies = $average >= (float) $level->min_grade;
            $attendanceMatches = $level->min_attendance === null
                || $attendanceRate === null
                || $attendanceRate >= (float) $level->min_attendance;

            if ($gradeQualifies && $attendanceMatches) {
                return $level;
            }
        }

        return $levels->last();
    }

    private function trend(Student $student, GradingPeriod $period, ?float $average): Trend
    {
        if ($average === null) {
            return Trend::Same;
        }

        $previous = GradingPeriod::query()
            ->where('school_year_id', $period->school_year_id)
            ->where('starts_on', '<', $period->starts_on)
            ->orderByDesc('starts_on')
            ->first();

        if ($previous === null) {
            return Trend::Same;
        }

        $previousAverage = PerformanceSummary::query()
            ->where('student_id', $student->id)
            ->where('grading_period_id', $previous->id)
            ->value('general_average');

        if ($previousAverage === null) {
            return Trend::Same;
        }

        $difference = $average - (float) $previousAverage;

        return match (true) {
            $difference > 0.5 => Trend::Up,
            $difference < -0.5 => Trend::Down,
            default => Trend::Same,
        };
    }
}
