<?php

namespace App\Observers;

use App\Models\Attendance;
use App\Models\GradingPeriod;
use App\Services\PerformanceEvaluator;

class AttendanceObserver
{
    public function saved(Attendance $attendance): void
    {
        $this->evaluate($attendance);
    }

    public function deleted(Attendance $attendance): void
    {
        $this->evaluate($attendance);
    }

    private function evaluate(Attendance $attendance): void
    {
        $student = $attendance->student()->first();
        $section = $student?->section()->first();

        if ($student === null || $section === null) {
            return;
        }

        $period = GradingPeriod::query()
            ->where('school_year_id', $section->school_year_id)
            ->whereDate('starts_on', '<=', $attendance->date)
            ->whereDate('ends_on', '>=', $attendance->date)
            ->where('status', 'open')
            ->first();

        if ($period === null) {
            return;
        }

        app(PerformanceEvaluator::class)->evaluate($student, $period);
    }
}
