<?php

namespace Tests\Unit;

use App\Enums\ScoreStatus;
use App\Models\Activity;
use App\Models\ClassSubject;
use App\Models\GradeLevel;
use App\Models\GradingComponent;
use App\Models\GradingPeriod;
use App\Models\SchoolYear;
use App\Models\Score;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Services\GradeCalculator;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradeCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_scores_count_as_zero_and_excused_scores_are_excluded(): void
    {
        $this->seed(ReferenceDataSeeder::class);
        [$student, $classSubject, $period] = $this->classroom();

        $quiz = GradingComponent::query()->where('name', 'Quiz')->first();
        $exam = GradingComponent::query()->where('name', 'Exam')->first();

        $missing = $this->activity($classSubject, $period, $quiz, 100);
        $excused = $this->activity($classSubject, $period, $quiz, 100);
        $examActivity = $this->activity($classSubject, $period, $exam, 50);

        Score::query()->create(['activity_id' => $missing->id, 'student_id' => $student->id, 'status' => ScoreStatus::Missing, 'score' => null]);
        Score::query()->create(['activity_id' => $excused->id, 'student_id' => $student->id, 'status' => ScoreStatus::Excused, 'score' => null]);
        Score::query()->create(['activity_id' => $examActivity->id, 'student_id' => $student->id, 'status' => ScoreStatus::Graded, 'score' => 40]);

        $result = app(GradeCalculator::class)->calculate($student, $classSubject, $period);

        $this->assertSame(40.0, $result['raw_grade']);
        $this->assertSame('F', $result['letter']);
    }

    public function test_weights_that_do_not_sum_to_100_are_rescaled(): void
    {
        $this->seed(ReferenceDataSeeder::class);
        [$student, $classSubject, $period] = $this->classroom();
        GradingComponent::query()->delete();

        $first = GradingComponent::query()->create(['subject_id' => $classSubject->subject_id, 'name' => 'A', 'weight' => 40]);
        $second = GradingComponent::query()->create(['subject_id' => $classSubject->subject_id, 'name' => 'B', 'weight' => 40]);

        $full = $this->activity($classSubject, $period, $first, 10);
        $half = $this->activity($classSubject, $period, $second, 10);
        Score::query()->create(['activity_id' => $full->id, 'student_id' => $student->id, 'status' => ScoreStatus::Graded, 'score' => 10]);
        Score::query()->create(['activity_id' => $half->id, 'student_id' => $student->id, 'status' => ScoreStatus::Graded, 'score' => 5]);

        $result = app(GradeCalculator::class)->calculate($student, $classSubject, $period);

        $this->assertSame(75.0, $result['raw_grade']);
    }

    public function test_a_zero_maximum_score_is_skipped(): void
    {
        $this->seed(ReferenceDataSeeder::class);
        [$student, $classSubject, $period] = $this->classroom();
        $component = GradingComponent::query()->where('name', 'Quiz')->first();

        Activity::withoutEvents(function () use ($classSubject, $period, $component, $student): void {
            $activity = Activity::query()->create([
                'class_subject_id' => $classSubject->id,
                'grading_period_id' => $period->id,
                'grading_component_id' => $component->id,
                'title' => 'Broken',
                'type' => 'quiz',
                'max_score' => 0,
            ]);
            Score::withoutEvents(fn () => Score::query()->create([
                'activity_id' => $activity->id,
                'student_id' => $student->id,
                'status' => ScoreStatus::Graded,
                'score' => 0,
            ]));
        });

        $result = app(GradeCalculator::class)->calculate($student, $classSubject, $period);

        $this->assertNull($result['raw_grade']);
    }

    /**
     * @return array{0: Student, 1: ClassSubject, 2: GradingPeriod}
     */
    private function classroom(): array
    {
        GradingComponent::query()->create(['subject_id' => null, 'name' => 'Quiz', 'weight' => 50]);
        GradingComponent::query()->create(['subject_id' => null, 'name' => 'Exam', 'weight' => 50]);

        $year = SchoolYear::query()->create(['name' => '2026-2027', 'starts_on' => '2026-06-01', 'ends_on' => '2027-03-31', 'is_current' => true]);
        $period = GradingPeriod::query()->create(['school_year_id' => $year->id, 'name' => 'Q1', 'starts_on' => '2026-06-01', 'ends_on' => '2026-08-15', 'status' => 'open']);
        $level = GradeLevel::query()->create(['name' => 'Grade 7', 'order' => 7]);
        $teacherUser = User::factory()->create();
        $teacher = Teacher::query()->create(['user_id' => $teacherUser->id, 'employee_no' => 'T-1']);
        $section = Section::query()->create(['grade_level_id' => $level->id, 'school_year_id' => $year->id, 'name' => 'A']);
        $studentUser = User::factory()->create();
        $student = Student::query()->create(['user_id' => $studentUser->id, 'student_no' => 'S-1', 'section_id' => $section->id, 'status' => 'active']);
        $subject = Subject::query()->create(['code' => 'MATH', 'name' => 'Math']);
        $classSubject = ClassSubject::query()->create(['section_id' => $section->id, 'subject_id' => $subject->id, 'teacher_id' => $teacher->id]);

        return [$student, $classSubject, $period];
    }

    private function activity(ClassSubject $classSubject, GradingPeriod $period, GradingComponent $component, float $max): Activity
    {
        return Activity::query()->create([
            'class_subject_id' => $classSubject->id,
            'grading_period_id' => $period->id,
            'grading_component_id' => $component->id,
            'title' => fake()->words(2, true),
            'type' => 'quiz',
            'max_score' => $max,
        ]);
    }
}
