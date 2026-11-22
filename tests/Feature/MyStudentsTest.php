<?php

namespace Tests\Feature;

use App\Enums\PeriodStatus;
use App\Enums\ScoreStatus;
use App\Filament\Teacher\Pages\MyStudents;
use App\Filament\Teacher\Pages\StudentRecord;
use App\Models\Activity;
use App\Models\GradingPeriod;
use App\Models\Score;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Support\BuildsSchool;
use Tests\TestCase;

class MyStudentsTest extends TestCase
{
    use BuildsSchool;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        Filament::setCurrentPanel(Filament::getPanel('teacher'));
    }

    public function test_teacher_sees_a_student_card_and_saves_an_edited_score(): void
    {
        $school = $this->school();
        $activity = $this->activity($school);
        $teacher = $school['teacher']->user;
        $teacher->assignRole('teacher');
        $school['student']->user->update(['name' => "Ana <script>alert('x')</script>"]);

        $html = Livewire::actingAs($teacher)
            ->test(MyStudents::class)
            ->assertSee('Ana')
            ->html();

        $this->assertStringNotContainsString("<script>alert('x')</script>", $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);

        Livewire::actingAs($teacher)
            ->test(StudentRecord::class, ['student' => $school['student']->id])
            ->set('rows.'.$activity->id.'.score', '8')
            ->set('rows.'.$activity->id.'.status', ScoreStatus::Graded->value)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas(Score::class, [
            'activity_id' => $activity->id,
            'student_id' => $school['student']->id,
            'score' => 8,
        ]);
    }

    public function test_teacher_student_directory_paginates_and_search_returns_to_first_page(): void
    {
        $school = $this->school();
        $teacher = $school['teacher']->user;
        $teacher->assignRole('teacher');

        Student::factory()->count(16)->create(['section_id' => $school['student']->section_id]);

        Livewire::actingAs($teacher)
            ->test(MyStudents::class)
            ->assertSee('Showing 1–16 of 17 students')
            ->call('gotoPage', 2)
            ->assertSee('Showing 17–17 of 17 students')
            ->set('query', $school['student']->student_no)
            ->call('applySearch')
            ->assertSee('Showing 1–1 of 1 students')
            ->assertSee($school['student']->student_no);
    }

    public function test_term_filter_shows_only_that_terms_scores(): void
    {
        $school = $this->school();
        $first = $this->activity($school);
        $section = $school['classSubject']->section;
        $secondPeriod = GradingPeriod::query()->create([
            'school_year_id' => $section->school_year_id,
            'name' => 'Second Semester',
            'starts_on' => '2026-08-16',
            'ends_on' => '2026-10-31',
            'status' => PeriodStatus::Open,
        ]);
        $second = Activity::query()->create([
            'class_subject_id' => $school['classSubject']->id,
            'grading_period_id' => $secondPeriod->id,
            'grading_component_id' => $first->grading_component_id,
            'title' => 'Final Exam',
            'type' => 'final',
            'max_score' => 100,
        ]);
        $teacher = $school['teacher']->user;
        $teacher->assignRole('teacher');

        Livewire::actingAs($teacher)
            ->test(StudentRecord::class, ['student' => $school['student']->id])
            ->assertSee($first->title)
            ->assertSee('Final Exam')
            ->set('term', (string) $second->grading_period_id)
            ->call('applyTerm')
            ->assertSee('Final Exam')
            ->assertDontSee($first->title);
    }

    public function test_score_above_the_maximum_is_rejected(): void
    {
        $school = $this->school();
        $activity = $this->activity($school);
        $teacher = $school['teacher']->user;
        $teacher->assignRole('teacher');

        Livewire::actingAs($teacher)
            ->test(StudentRecord::class, ['student' => $school['student']->id])
            ->set('rows.'.$activity->id.'.score', '11')
            ->set('rows.'.$activity->id.'.status', ScoreStatus::Graded->value)
            ->call('save')
            ->assertHasErrors(['scores']);

        $this->assertDatabaseCount(Score::class, 0);
    }

    public function test_closed_term_score_is_not_saved(): void
    {
        $school = $this->school();
        $activity = $this->activity($school);
        $school['period']->update(['status' => PeriodStatus::Closed]);
        $teacher = $school['teacher']->user;
        $teacher->assignRole('teacher');

        Livewire::actingAs($teacher)
            ->test(StudentRecord::class, ['student' => $school['student']->id])
            ->set('rows.'.$activity->id.'.score', '8')
            ->set('rows.'.$activity->id.'.status', ScoreStatus::Graded->value)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseCount(Score::class, 0);
    }

    public function test_another_teacher_receives_403_for_a_student_they_do_not_teach(): void
    {
        $school = $this->school();
        $teacher = $school['teacher']->user;
        $teacher->assignRole('teacher');
        $other = User::factory()->create();
        $other->assignRole('teacher');
        Teacher::query()->create([
            'user_id' => $other->id,
            'employee_no' => 'E-other',
        ]);

        $this->actingAs($other)
            ->get(StudentRecord::getUrl(['student' => $school['student']]))
            ->assertForbidden();
    }
}
