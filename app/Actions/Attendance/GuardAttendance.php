<?php

namespace App\Actions\Attendance;

use App\Models\Attendance;
use App\Models\GradingPeriod;
use Illuminate\Validation\ValidationException;

class GuardAttendance
{
    public function __invoke(Attendance $attendance): void
    {
        $attendance->loadMissing('classSubject.section', 'student');

        if ($attendance->student !== null && $attendance->student->section_id !== $attendance->classSubject?->section_id) {
            throw ValidationException::withMessages([
                'student_id' => 'The student is not enrolled in this class section.',
            ]);
        }

        $period = GradingPeriod::query()
            ->where('school_year_id', $attendance->classSubject?->section?->school_year_id)
            ->whereDate('starts_on', '<=', $attendance->date)
            ->whereDate('ends_on', '>=', $attendance->date)
            ->first();

        if ($period === null || ! $period->acceptsEntries()) {
            throw ValidationException::withMessages([
                'date' => 'Attendance can only be recorded on a date inside an open grading period.',
            ]);
        }
    }
}
