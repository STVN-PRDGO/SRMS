<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Enums\PeriodStatus;
use App\Enums\StudentStatus;
use App\Models\Activity;
use App\Models\Attendance;
use App\Models\ClassSubject;
use App\Models\Grade;
use App\Models\GradingPeriod;
use App\Models\PerformanceLevel;
use App\Models\PerformanceSummary;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;

class AnalyticsService
{
    /**
     * Overview stats for Admin dashboard.
     *
     * @return array{
     *     total_students: int,
     *     total_teachers: int,
     *     total_sections: int,
     *     active_school_year: string,
     *     active_period: ?GradingPeriod,
     *     school_average: ?float,
     *     attendance_rate: ?float
     * }
     */
    public function getAdminOverview(): array
    {
        $currentYear = SchoolYear::query()->where('is_current', true)->first();
        $activePeriod = GradingPeriod::query()
            ->when($currentYear, fn ($q) => $q->where('school_year_id', $currentYear->id))
            ->where('status', PeriodStatus::Open)
            ->first();

        $latestPeriod = $activePeriod ?? GradingPeriod::query()
            ->when($currentYear, fn ($q) => $q->where('school_year_id', $currentYear->id))
            ->orderByDesc('starts_on')
            ->first();

        $avgGrade = null;
        if ($latestPeriod) {
            $avgGrade = Grade::query()
                ->where('grading_period_id', $latestPeriod->id)
                ->whereNotNull('raw_grade')
                ->avg('raw_grade');
        }

        $attendanceRate = null;
        if ($latestPeriod) {
            $counted = Attendance::query()
                ->whereDate('date', '>=', $latestPeriod->starts_on)
                ->whereDate('date', '<=', $latestPeriod->ends_on)
                ->whereIn('status', [AttendanceStatus::Present, AttendanceStatus::Late, AttendanceStatus::Absent])
                ->get();

            if ($counted->isNotEmpty()) {
                $present = $counted->whereIn('status', [AttendanceStatus::Present, AttendanceStatus::Late])->count();
                $attendanceRate = round(($present / $counted->count()) * 100, 1);
            }
        }

        return [
            'total_students' => Student::query()->where('status', StudentStatus::Active)->count(),
            'total_teachers' => Teacher::query()->count(),
            'total_sections' => Section::query()->count(),
            'active_school_year' => $currentYear?->name ?? 'None set',
            'active_period' => $latestPeriod,
            'school_average' => $avgGrade !== null ? round((float) $avgGrade, 2) : null,
            'attendance_rate' => $attendanceRate,
        ];
    }

    /**
     * Distribution of students across performance levels for a given period.
     *
     * @return array{labels: list<string>, data: list<int>}
     */
    public function getPerformanceLevelDistribution(?int $periodId = null): array
    {
        $period = $periodId
            ? GradingPeriod::query()->find($periodId)
            : GradingPeriod::query()->where('status', PeriodStatus::Open)->first()
                ?? GradingPeriod::query()->orderByDesc('starts_on')->first();

        if (! $period) {
            return ['labels' => [], 'data' => []];
        }

        $levels = PerformanceLevel::query()->orderBy('order')->get();
        $counts = PerformanceSummary::query()
            ->where('grading_period_id', $period->id)
            ->get()
            ->groupBy('performance_level_id');

        $labels = [];
        $data = [];

        foreach ($levels as $level) {
            $labels[] = $level->name;
            $data[] = isset($counts[$level->id]) ? $counts[$level->id]->count() : 0;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Average grade per section in a period.
     *
     * @return array{labels: list<string>, data: list<float>}
     */
    public function getSectionAveragesChart(?int $periodId = null): array
    {
        $period = $periodId
            ? GradingPeriod::query()->find($periodId)
            : GradingPeriod::query()->where('status', PeriodStatus::Open)->first()
                ?? GradingPeriod::query()->orderByDesc('starts_on')->first();

        if (! $period) {
            return ['labels' => [], 'data' => []];
        }

        $grades = Grade::query()
            ->where('grading_period_id', $period->id)
            ->whereNotNull('raw_grade')
            ->with('classSubject.section')
            ->get();

        $sections = Section::query()->orderBy('name')->get();
        $labels = [];
        $data = [];

        foreach ($sections as $section) {
            $sectionGrades = $grades->filter(fn (Grade $g) => $g->classSubject?->section_id === $section->id);
            if ($sectionGrades->isNotEmpty()) {
                $labels[] = $section->name;
                $data[] = round((float) $sectionGrades->avg('raw_grade'), 2);
            }
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * School-wide attendance counts by status.
     *
     * @return array{labels: list<string>, data: list<int>}
     */
    public function getAttendanceDistribution(?int $periodId = null): array
    {
        $period = $periodId
            ? GradingPeriod::query()->find($periodId)
            : GradingPeriod::query()->where('status', PeriodStatus::Open)->first()
                ?? GradingPeriod::query()->orderByDesc('starts_on')->first();

        if (! $period) {
            return ['labels' => [], 'data' => []];
        }

        $rows = Attendance::query()
            ->whereDate('date', '>=', $period->starts_on)
            ->whereDate('date', '<=', $period->ends_on)
            ->get();

        return [
            'labels' => ['Present', 'Late', 'Absent', 'Excused'],
            'data' => [
                $rows->where('status', AttendanceStatus::Present)->count(),
                $rows->where('status', AttendanceStatus::Late)->count(),
                $rows->where('status', AttendanceStatus::Absent)->count(),
                $rows->where('status', AttendanceStatus::Excused)->count(),
            ],
        ];
    }

    /**
     * Teacher overview stats.
     *
     * @return array{
     *     classes_count: int,
     *     students_count: int,
     *     average_grade: ?float,
     *     pending_activities: int,
     *     at_risk_count: int
     * }
     */
    public function getTeacherOverview(User $user): array
    {
        $classes = ClassSubject::query()->visibleTo($user)->with('section')->get();
        $classIds = $classes->pluck('id');
        $sectionIds = $classes->pluck('section_id')->unique();

        $studentsCount = Student::query()
            ->whereIn('section_id', $sectionIds)
            ->where('status', StudentStatus::Active)
            ->count();

        $avgGrade = Grade::query()
            ->whereIn('class_subject_id', $classIds)
            ->whereNotNull('raw_grade')
            ->avg('raw_grade');

        $pending = Activity::query()
            ->whereIn('class_subject_id', $classIds)
            ->whereHas('gradingPeriod', fn ($q) => $q->where('status', PeriodStatus::Open))
            ->withCount('scores')
            ->with(['classSubject.section' => fn ($q) => $q->withCount('students')])
            ->get()
            ->filter(fn (Activity $a) => $a->scores_count < ($a->classSubject->section->students_count ?? 0))
            ->count();

        $bottomLevel = PerformanceLevel::query()->orderBy('order', 'desc')->first();
        $atRiskCount = 0;
        if ($bottomLevel) {
            $atRiskCount = PerformanceSummary::query()
                ->where('performance_level_id', $bottomLevel->id)
                ->whereHas('student', fn ($q) => $q->whereIn('section_id', $sectionIds))
                ->count();
        }

        return [
            'classes_count' => $classes->count(),
            'students_count' => $studentsCount,
            'average_grade' => $avgGrade !== null ? round((float) $avgGrade, 2) : null,
            'pending_activities' => $pending,
            'at_risk_count' => $atRiskCount,
        ];
    }

    /**
     * Comparison of class averages for a teacher.
     *
     * @return array{labels: list<string>, data: list<float>}
     */
    public function getTeacherClassComparison(User $user): array
    {
        $classes = ClassSubject::query()
            ->visibleTo($user)
            ->with('subject', 'section')
            ->get();

        $labels = [];
        $data = [];

        foreach ($classes as $classSubject) {
            $avg = Grade::query()
                ->where('class_subject_id', $classSubject->id)
                ->whereNotNull('raw_grade')
                ->avg('raw_grade');

            if ($avg !== null) {
                $labels[] = ($classSubject->subject->name ?? 'Subject').' ('.($classSubject->section->name ?? 'Section').')';
                $data[] = round((float) $avg, 2);
            }
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Letter grade distribution across classes for a teacher.
     *
     * @return array{labels: list<string>, data: list<int>}
     */
    public function getTeacherGradeDistribution(User $user): array
    {
        $classIds = ClassSubject::query()->visibleTo($user)->pluck('id');
        $grades = Grade::query()
            ->whereIn('class_subject_id', $classIds)
            ->whereNotNull('raw_grade')
            ->pluck('raw_grade');

        $distribution = ['A (90-100)' => 0, 'B (85-89)' => 0, 'C (80-84)' => 0, 'D (75-79)' => 0, 'F (<75)' => 0];

        foreach ($grades as $grade) {
            $val = (float) $grade;
            match (true) {
                $val >= 90 => $distribution['A (90-100)']++,
                $val >= 85 => $distribution['B (85-89)']++,
                $val >= 80 => $distribution['C (80-84)']++,
                $val >= 75 => $distribution['D (75-79)']++,
                default => $distribution['F (<75)']++,
            };
        }

        return [
            'labels' => array_keys($distribution),
            'data' => array_values($distribution),
        ];
    }

    /**
     * Student summary for student dashboard.
     *
     * @return array{
     *     general_average: ?float,
     *     performance_level: ?string,
     *     attendance_rate: ?float,
     *     activity_completion_rate: ?float,
     *     trend: ?string,
     *     period_name: string
     * }
     */
    public function getStudentOverview(Student $student): array
    {
        $latestSummary = PerformanceSummary::query()
            ->where('student_id', $student->id)
            ->with('gradingPeriod', 'performanceLevel')
            ->orderByDesc('computed_at')
            ->first();

        return [
            'general_average' => $latestSummary?->general_average !== null ? (float) $latestSummary->general_average : null,
            'performance_level' => $latestSummary?->performanceLevel?->name ?? 'Pending evaluation',
            'attendance_rate' => $latestSummary?->attendance_rate !== null ? (float) $latestSummary->attendance_rate : null,
            'activity_completion_rate' => $latestSummary?->activity_completion_rate !== null ? (float) $latestSummary->activity_completion_rate : null,
            'trend' => $latestSummary?->trend?->value ?? 'same',
            'period_name' => $latestSummary?->gradingPeriod?->name ?? 'Current Quarter',
        ];
    }

    /**
     * Student subject grades breakdown for the latest period.
     *
     * @return array{labels: list<string>, data: list<float>}
     */
    public function getStudentSubjectBreakdown(Student $student): array
    {
        $latestPeriod = GradingPeriod::query()->where('status', PeriodStatus::Open)->first()
            ?? GradingPeriod::query()->orderByDesc('starts_on')->first();

        if (! $latestPeriod) {
            return ['labels' => [], 'data' => []];
        }

        $grades = Grade::query()
            ->where('student_id', $student->id)
            ->where('grading_period_id', $latestPeriod->id)
            ->with('classSubject.subject')
            ->get();

        $labels = [];
        $data = [];

        foreach ($grades as $grade) {
            $labels[] = $grade->classSubject?->subject?->name ?? 'Subject';
            $data[] = $grade->raw_grade !== null ? (float) $grade->raw_grade : 0.0;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }
}
