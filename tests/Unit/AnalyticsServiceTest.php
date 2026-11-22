<?php

namespace Tests\Unit;

use App\Enums\AttendanceStatus;
use App\Enums\ScoreStatus;
use App\Models\Attendance;
use App\Models\Score;
use App\Services\AnalyticsService;
use App\Services\GradeCalculator;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsSchool;
use Tests\TestCase;

class AnalyticsServiceTest extends TestCase
{
    use BuildsSchool;
    use RefreshDatabase;

    public function test_admin_and_teacher_analytics_compute_accurately(): void
    {
        $this->seed(ReferenceDataSeeder::class);
        $school = $this->school();
        $activity = $this->activity($school);

        Score::query()->create([
            'activity_id' => $activity->id,
            'student_id' => $school['student']->id,
            'status' => ScoreStatus::Graded,
            'score' => 8.5,
        ]);

        Attendance::query()->create([
            'class_subject_id' => $school['classSubject']->id,
            'student_id' => $school['student']->id,
            'date' => '2026-06-15',
            'status' => AttendanceStatus::Present,
        ]);

        app(GradeCalculator::class)->calculate($school['student'], $school['classSubject'], $school['period']);

        $service = app(AnalyticsService::class);
        $adminOverview = $service->getAdminOverview();

        $this->assertGreaterThanOrEqual(1, $adminOverview['total_students']);
        $this->assertGreaterThanOrEqual(1, $adminOverview['total_teachers']);
        $this->assertGreaterThanOrEqual(1, $adminOverview['total_sections']);

        $teacherOverview = $service->getTeacherOverview($school['teacher']->user);
        $this->assertSame(1, $teacherOverview['classes_count']);
        $this->assertSame(1, $teacherOverview['students_count']);
    }
}
