<?php

namespace Tests\Feature;

use App\Actions\Grades\RecalculateStudentGrade;
use App\Actions\Grading\ApplyGradingSystem;
use App\Enums\GradingTrack;
use App\Enums\ScoreStatus;
use App\Filament\Admin\Pages\GradingSystem;
use App\Models\Grade;
use App\Models\GradingComponent;
use App\Models\SchoolSetting;
use App\Models\Score;
use App\Models\User;
use App\Support\PhilippineGrading;
use Database\Seeders\RoleAndPermissionSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Support\BuildsSchool;
use Tests\TestCase;

class GradingSystemTest extends TestCase
{
    use BuildsSchool;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_high_school_transmutes_the_weighted_score_and_writes_the_remark(): void
    {
        $school = $this->school();
        $activity = $this->activity($school);
        app(ApplyGradingSystem::class)(GradingTrack::HighSchool);

        Score::query()->create([
            'activity_id' => $activity->id,
            'student_id' => $school['student']->id,
            'score' => 9,
            'status' => ScoreStatus::Graded,
        ]);

        app(RecalculateStudentGrade::class)(
            $school['student']->id,
            $school['classSubject']->id,
            $school['period']->id,
        );

        $grade = Grade::query()->first();
        $this->assertSame('93.00', $grade->raw_grade);
        $this->assertSame('A', $grade->letter);
        $this->assertSame('Outstanding', $grade->remark);
        $this->assertSame(20.0, (float) GradingComponent::query()->where('name', 'Quizzes')->value('weight'));
    }

    public function test_college_keeps_the_percentage_and_uses_the_point_remark(): void
    {
        $school = $this->school();
        $activity = $this->activity($school);
        app(ApplyGradingSystem::class)(GradingTrack::College);

        Score::query()->create([
            'activity_id' => $activity->id,
            'student_id' => $school['student']->id,
            'score' => 9,
            'status' => ScoreStatus::Graded,
        ]);

        app(RecalculateStudentGrade::class)(
            $school['student']->id,
            $school['classSubject']->id,
            $school['period']->id,
        );

        $grade = Grade::query()->first();
        $this->assertSame('90.00', $grade->raw_grade);
        $this->assertSame('1.75', $grade->letter);
        $this->assertSame('Good', $grade->remark);
    }

    public function test_admin_can_apply_the_grading_system_from_the_page(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        Livewire::actingAs($admin)
            ->test(GradingSystem::class)
            ->set('data.grading_track', GradingTrack::College->value)
            ->callAction('apply')
            ->assertHasNoErrors();

        $this->assertSame(GradingTrack::College, SchoolSetting::current()->grading_track);
        $this->assertSame(25.0, (float) GradingComponent::query()->where('name', 'Final')->value('weight'));
    }

    public function test_deped_transmutation_matches_the_published_table(): void
    {
        $this->assertSame(100.0, PhilippineGrading::quarterlyGrade(100));
        $this->assertSame(75.0, PhilippineGrading::quarterlyGrade(60));
        $this->assertSame(74.0, PhilippineGrading::quarterlyGrade(56));
        $this->assertSame(60.0, PhilippineGrading::quarterlyGrade(0));
    }
}
