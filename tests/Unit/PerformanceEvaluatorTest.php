<?php

namespace Tests\Unit;

use App\Actions\Grades\RecalculateStudentGrade;
use App\Enums\AttendanceStatus;
use App\Enums\ScoreStatus;
use App\Enums\Trend;
use App\Models\Attendance;
use App\Models\Score;
use App\Services\GradeCalculator;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsSchool;
use Tests\TestCase;

class PerformanceEvaluatorTest extends TestCase
{
    use BuildsSchool;
    use RefreshDatabase;

    public function test_attendance_and_completion_feed_the_performance_level(): void
    {
        $this->seed(ReferenceDataSeeder::class);
        $school = $this->school();
        $activity = $this->activity($school);

        Score::query()->create([
            'activity_id' => $activity->id,
            'student_id' => $school['student']->id,
            'status' => ScoreStatus::Graded,
            'score' => 9,
        ]);

        Attendance::query()->create([
            'class_subject_id' => $school['classSubject']->id,
            'student_id' => $school['student']->id,
            'date' => '2026-06-10',
            'status' => AttendanceStatus::Present,
        ]);

        $grade = app(GradeCalculator::class)->calculate($school['student'], $school['classSubject'], $school['period']);
        $this->assertSame(90.0, $grade['raw_grade']);

        app(RecalculateStudentGrade::class)(
            $school['student']->id,
            $school['classSubject']->id,
            $school['period']->id,
        );

        $summary = $school['student']->performanceSummaries()->first();
        $this->assertSame('90.00', $summary->general_average);
        $this->assertSame('100.00', $summary->attendance_rate);
        $this->assertSame('100.00', $summary->activity_completion_rate);
        $this->assertSame('Outstanding', $summary->performanceLevel->name);
        $this->assertSame(Trend::Same, $summary->trend);
    }

    public function test_high_grade_with_insufficient_attendance_cascades_to_matching_level(): void
    {
        $this->seed(ReferenceDataSeeder::class);
        $school = $this->school();
        $activity = $this->activity($school);

        Score::query()->create([
            'activity_id' => $activity->id,
            'student_id' => $school['student']->id,
            'status' => ScoreStatus::Graded,
            'score' => 9.2,
        ]);

        // 1 present, 1 absent = 50% attendance
        Attendance::query()->create([
            'class_subject_id' => $school['classSubject']->id,
            'student_id' => $school['student']->id,
            'date' => '2026-06-10',
            'status' => AttendanceStatus::Present,
        ]);
        Attendance::query()->create([
            'class_subject_id' => $school['classSubject']->id,
            'student_id' => $school['student']->id,
            'date' => '2026-06-11',
            'status' => AttendanceStatus::Absent,
        ]);

        app(RecalculateStudentGrade::class)(
            $school['student']->id,
            $school['classSubject']->id,
            $school['period']->id,
        );

        $summary = $school['student']->performanceSummaries()->first();
        $this->assertSame('92.00', $summary->general_average);
        $this->assertSame('50.00', $summary->attendance_rate);
        // With 50% attendance (< 75%), student cascades down to Needs Improvement despite high grade
        $this->assertSame('Needs Improvement', $summary->performanceLevel->name);
    }
}
