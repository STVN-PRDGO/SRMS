<?php

namespace Tests\Support;

use App\Enums\ActivityType;
use App\Enums\PeriodStatus;
use App\Models\Activity;
use App\Models\ClassSubject;
use App\Models\GradeLevel;
use App\Models\GradingComponent;
use App\Models\GradingPeriod;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;

trait BuildsSchool
{
    /**
     * @return array{teacher: Teacher, student: Student, classSubject: ClassSubject, period: GradingPeriod, subject: Subject}
     */
    protected function school(): array
    {
        $suffix = fake()->unique()->numerify('####');
        $year = SchoolYear::query()->create([
            'name' => 'Year '.$suffix,
            'starts_on' => '2026-06-01',
            'ends_on' => '2027-03-31',
            'is_current' => false,
        ]);
        $period = GradingPeriod::query()->create([
            'school_year_id' => $year->id,
            'name' => 'Q1',
            'starts_on' => '2026-06-01',
            'ends_on' => '2026-08-15',
            'status' => PeriodStatus::Open,
        ]);
        $level = GradeLevel::query()->firstOrCreate(['name' => 'Grade 7'], ['order' => 7]);
        $teacher = Teacher::query()->create([
            'user_id' => User::factory()->create()->id,
            'employee_no' => 'E-'.$suffix,
        ]);
        $section = Section::query()->create([
            'grade_level_id' => $level->id,
            'school_year_id' => $year->id,
            'name' => 'Section '.$suffix,
        ]);
        $student = Student::query()->create([
            'user_id' => User::factory()->create()->id,
            'student_no' => 'S-'.$suffix,
            'section_id' => $section->id,
            'status' => 'active',
        ]);
        $subject = Subject::query()->create(['code' => 'SUB-'.$suffix, 'name' => 'Subject '.$suffix]);
        $classSubject = ClassSubject::query()->create([
            'section_id' => $section->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
        ]);

        if (! GradingComponent::query()->where('name', 'Quiz')->exists()) {
            GradingComponent::query()->create(['name' => 'Quiz', 'weight' => 100]);
        }

        return compact('teacher', 'student', 'classSubject', 'period', 'subject');
    }

    /**
     * @param  array{classSubject: ClassSubject, period: GradingPeriod}  $school
     */
    protected function activity(array $school): Activity
    {
        return Activity::query()->create([
            'class_subject_id' => $school['classSubject']->id,
            'grading_period_id' => $school['period']->id,
            'grading_component_id' => GradingComponent::query()->where('name', 'Quiz')->first()->id,
            'title' => 'Quiz 1',
            'type' => ActivityType::Quiz,
            'max_score' => 10,
        ]);
    }
}
