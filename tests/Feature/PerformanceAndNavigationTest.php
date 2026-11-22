<?php

namespace Tests\Feature;

use App\Actions\Scores\SaveScores;
use App\Filament\Admin\AdminNavigationGroup;
use App\Filament\Admin\Resources\ClassSubjectResource;
use App\Filament\Admin\Resources\StudentResource;
use App\Filament\Admin\Resources\UserResource;
use App\Filament\Admin\Widgets\AdminOverviewStatsWidget;
use App\Filament\Student\Pages\Profile as StudentProfile;
use App\Filament\Student\Widgets\StudentOverviewStatsWidget;
use App\Filament\Teacher\Resources\ScoreResource;
use App\Filament\Teacher\Widgets\TeacherOverviewStatsWidget;
use App\Jobs\RecalculateStudentGradeJob;
use App\Models\GradeScale;
use App\Models\GradingComponent;
use App\Models\PerformanceLevel;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\ReferenceDataSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\Support\BuildsSchool;
use Tests\TestCase;

class PerformanceAndNavigationTest extends TestCase
{
    use BuildsSchool;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_prevent_lazy_loading_is_enabled_in_local_and_testing(): void
    {
        $this->assertTrue(Model::preventsLazyLoading());
    }

    public function test_navigation_is_grouped_around_daily_tasks(): void
    {
        $this->assertSame(AdminNavigationGroup::People, StudentResource::getNavigationGroup());
        $this->assertSame(AdminNavigationGroup::Classes, ClassSubjectResource::getNavigationGroup());
        $this->assertSame(AdminNavigationGroup::Access, UserResource::getNavigationGroup());
        $this->assertSame('Accounts', UserResource::getNavigationLabel());
        $this->assertSame('Classes', ClassSubjectResource::getNavigationLabel());
        $this->assertFalse(ScoreResource::shouldRegisterNavigation());
        $this->assertFalse(StudentProfile::shouldRegisterNavigation());
    }

    public function test_student_panel_has_global_search_disabled(): void
    {
        $studentPanel = Filament::getPanel('student');
        $this->assertNull($studentPanel->getGlobalSearchProvider());
    }

    public function test_widgets_have_polling_disabled(): void
    {
        $getPoll = function (string $class): ?string {
            $reflection = new \ReflectionClass($class);
            $prop = $reflection->getProperty('pollingInterval');
            $prop->setAccessible(true);
            $instance = new $class;

            return $prop->getValue($instance);
        };

        $this->assertNull($getPoll(AdminOverviewStatsWidget::class));
        $this->assertNull($getPoll(TeacherOverviewStatsWidget::class));
        $this->assertNull($getPoll(StudentOverviewStatsWidget::class));
    }

    public function test_reference_data_caching_and_invalidation(): void
    {
        Cache::flush();

        // 1. Grade scales caching
        $this->assertFalse(Cache::has('grade_scales.all'));
        $scale = GradeScale::query()->first();
        $this->assertNotNull($scale);

        // Fetching populates it
        $cachedScales = GradeScale::allCached();
        $this->assertTrue(Cache::has('grade_scales.all'));
        $this->assertNotEmpty($cachedScales);

        // Updating model invalidates cache
        $scale->update(['remark' => 'Updated Remark']);
        $this->assertFalse(Cache::has('grade_scales.all'));

        // 2. Performance levels caching
        $this->assertFalse(Cache::has('performance_levels.all'));
        $level = PerformanceLevel::query()->first();
        $this->assertNotNull($level);

        $cachedLevels = PerformanceLevel::allCached();
        $this->assertTrue(Cache::has('performance_levels.all'));
        $this->assertNotEmpty($cachedLevels);

        $level->update(['name' => 'Updated Name']);
        $this->assertFalse(Cache::has('performance_levels.all'));

        // 3. Grading components caching
        $this->assertFalse(Cache::has('grading_components.all'));
        $component = GradingComponent::query()->first();
        if ($component) {
            $cachedComponents = GradingComponent::allCached();
            $this->assertTrue(Cache::has('grading_components.all'));
            $this->assertNotEmpty($cachedComponents);

            $component->update(['name' => 'Updated Component']);
            $this->assertFalse(Cache::has('grading_components.all'));
        }
    }

    public function test_save_scores_runs_in_single_batch_transaction_and_clears_teacher_cache(): void
    {
        $school = $this->school();
        $teacher = $school['teacher']->user;
        $teacher->assignRole('teacher');
        $activity = $this->activity($school);

        $student2 = Student::factory()->create(['section_id' => $school['classSubject']->section_id]);

        Queue::fake();

        Cache::put("dash.teacher.{$teacher->id}", ['cached' => true], 60);
        $this->assertTrue(Cache::has("dash.teacher.{$teacher->id}"));

        $entries = [
            [
                'student_id' => $school['student']->id,
                'score' => 9,
                'status' => 'graded',
                'remarks' => 'Great work',
            ],
            [
                'student_id' => $student2->id,
                'score' => 8,
                'status' => 'graded',
                'remarks' => 'Good effort',
            ],
        ];

        DB::flushQueryLog();
        DB::enableQueryLog();

        app(SaveScores::class)($activity, $entries, $teacher);

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Check that scores were saved
        $this->assertDatabaseHas('scores', [
            'activity_id' => $activity->id,
            'student_id' => $school['student']->id,
            'score' => 9,
        ]);
        $this->assertDatabaseHas('scores', [
            'activity_id' => $activity->id,
            'student_id' => $student2->id,
            'score' => 8,
        ]);

        // Asserts batch upsert was executed (not individual queries per row)
        $upsertQueries = collect($queries)->filter(fn ($q) => str_contains(strtolower($q['query']), 'insert into "scores"') || str_contains(strtolower($q['query']), 'insert into `scores`'));
        $this->assertLessThanOrEqual(2, $upsertQueries->count());

        // Asserts teacher dashboard cache was invalidated
        $this->assertFalse(Cache::has("dash.teacher.{$teacher->id}"));

        // Asserts recalculation jobs were dispatched for each affected student
        Queue::assertPushed(RecalculateStudentGradeJob::class, 2);
    }

    public function test_dashboard_visits_stay_within_query_budget_and_do_not_trigger_lazy_loading(): void
    {
        $school = $this->school();
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $teacher = $school['teacher']->user;
        $teacher->assignRole('teacher');

        $student = $school['student']->user;
        $student->assignRole('student');

        // Test Student dashboard under budget and no lazy loading exception
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->flushSession();
        $this->actingAs($student->fresh())->get('/student')->assertOk();
        $studentQueries = count(DB::getQueryLog());
        DB::disableQueryLog();
        $this->assertLessThanOrEqual(20, $studentQueries);

        // Test Teacher dashboard under budget and no lazy loading exception
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->flushSession();
        $this->actingAs($teacher->fresh())->get('/teacher')->assertOk();
        $teacherQueries = count(DB::getQueryLog());
        DB::disableQueryLog();
        $this->assertLessThanOrEqual(20, $teacherQueries);

        // Test Admin dashboard under budget and no lazy loading exception
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->flushSession();
        $this->actingAs($admin->fresh())->get('/admin')->assertOk();
        $adminQueries = count(DB::getQueryLog());
        DB::disableQueryLog();
        $this->assertLessThanOrEqual(20, $adminQueries);
    }
}
