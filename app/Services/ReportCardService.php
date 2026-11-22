<?php

namespace App\Services;

use App\Models\GradingPeriod;
use App\Models\Student;
use Illuminate\Support\Facades\View;

class ReportCardService
{
    public function html(Student $student, GradingPeriod $period): string
    {
        $student->loadMissing('user', 'section.gradeLevel', 'section.schoolYear');

        $grades = $student->grades()
            ->where('grading_period_id', $period->id)
            ->with('classSubject.subject')
            ->get();

        $summary = $student->performanceSummaries()
            ->where('grading_period_id', $period->id)
            ->with('performanceLevel')
            ->first();

        return View::make('reports.report-card', [
            'student' => $student,
            'period' => $period,
            'grades' => $grades,
            'summary' => $summary,
        ])->render();
    }
}
