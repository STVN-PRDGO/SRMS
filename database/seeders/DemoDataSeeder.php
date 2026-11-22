<?php

namespace Database\Seeders;

use App\Actions\Grades\RecalculateStudentGrade;
use App\Enums\ActivityType;
use App\Enums\AttendanceStatus;
use App\Enums\PeriodStatus;
use App\Enums\ScoreStatus;
use App\Enums\StudentStatus;
use App\Models\Activity;
use App\Models\Attendance;
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
use App\Services\PerformanceEvaluator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        if (SchoolYear::query()->where('name', '2026-2027')->exists()) {
            return;
        }

        $teacherRole = Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);
        $studentRole = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

        // 1. School Year & Grading Periods
        $schoolYear = SchoolYear::query()->create([
            'name' => '2026-2027',
            'starts_on' => '2026-06-01',
            'ends_on' => '2027-03-31',
            'is_current' => true,
        ]);

        $q1 = GradingPeriod::query()->create([
            'school_year_id' => $schoolYear->id,
            'name' => '1st Quarter',
            'starts_on' => '2026-06-01',
            'ends_on' => '2026-08-15',
            'status' => PeriodStatus::Open,
        ]);

        $q2 = GradingPeriod::query()->create([
            'school_year_id' => $schoolYear->id,
            'name' => '2nd Quarter',
            'starts_on' => '2026-08-16',
            'ends_on' => '2026-10-31',
            'status' => PeriodStatus::Open,
        ]);

        $q3 = GradingPeriod::query()->create([
            'school_year_id' => $schoolYear->id,
            'name' => '3rd Quarter',
            'starts_on' => '2026-11-01',
            'ends_on' => '2027-01-15',
            'status' => PeriodStatus::Open,
        ]);

        $q4 = GradingPeriod::query()->create([
            'school_year_id' => $schoolYear->id,
            'name' => '4th Quarter',
            'starts_on' => '2027-01-16',
            'ends_on' => '2027-03-31',
            'status' => PeriodStatus::Draft,
        ]);

        // 2. Grade Levels
        $grade7 = GradeLevel::query()->firstOrCreate(['name' => 'Grade 7'], ['order' => 7]);
        $grade8 = GradeLevel::query()->firstOrCreate(['name' => 'Grade 8'], ['order' => 8]);

        // 3. Teachers
        $teachersData = [
            ['name' => 'Prof. Elena Vance', 'email' => 'teacher@example.com', 'emp' => 'EMP-2026-001', 'dept' => 'Mathematics'],
            ['name' => 'Prof. Gordon Freeman', 'email' => 'teacher2@example.com', 'emp' => 'EMP-2026-002', 'dept' => 'Science'],
            ['name' => 'Prof. Alyx Vance', 'email' => 'teacher3@example.com', 'emp' => 'EMP-2026-003', 'dept' => 'English'],
            ['name' => 'Prof. Barney Calhoun', 'email' => 'teacher4@example.com', 'emp' => 'EMP-2026-004', 'dept' => 'Social Sciences'],
        ];

        $teachers = [];
        foreach ($teachersData as $tData) {
            $user = User::query()->firstOrCreate(
                ['email' => $tData['email']],
                [
                    'name' => $tData['name'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'must_change_password' => false,
                ]
            );

            if (! $user->hasRole($teacherRole)) {
                $user->assignRole($teacherRole);
            }

            $teachers[$tData['email']] = Teacher::query()->firstOrCreate(
                ['user_id' => $user->id],
                [
                    'employee_no' => $tData['emp'],
                    'department' => $tData['dept'],
                    'contact_no' => '+63 912 345 6789',
                ]
            );
        }

        // 4. Sections
        $sec7Jude = Section::query()->create([
            'grade_level_id' => $grade7->id,
            'school_year_id' => $schoolYear->id,
            'name' => '7 - St. Jude',
            'adviser_teacher_id' => $teachers['teacher@example.com']->id,
        ]);

        $sec7Thomas = Section::query()->create([
            'grade_level_id' => $grade7->id,
            'school_year_id' => $schoolYear->id,
            'name' => '7 - St. Thomas',
            'adviser_teacher_id' => $teachers['teacher2@example.com']->id,
        ]);

        $sec8Francis = Section::query()->create([
            'grade_level_id' => $grade8->id,
            'school_year_id' => $schoolYear->id,
            'name' => '8 - St. Francis',
            'adviser_teacher_id' => $teachers['teacher3@example.com']->id,
        ]);

        $sec8Claire = Section::query()->create([
            'grade_level_id' => $grade8->id,
            'school_year_id' => $schoolYear->id,
            'name' => '8 - St. Claire',
            'adviser_teacher_id' => $teachers['teacher4@example.com']->id,
        ]);

        // 5. Subjects
        $subjectsData = [
            ['code' => 'MATH101', 'name' => 'Mathematics 7', 'desc' => 'Foundations of Algebra, Geometry and Statistics'],
            ['code' => 'SCI101', 'name' => 'General Science 7', 'desc' => 'Integrated Earth Science, Biology, and Physics'],
            ['code' => 'ENG101', 'name' => 'English & Literature 7', 'desc' => 'Grammar, Reading Comprehension, and Speech'],
            ['code' => 'SOC101', 'name' => 'Social Studies 7', 'desc' => 'Philippine History, Culture and Society'],
            ['code' => 'CS101', 'name' => 'Computer Science 7', 'desc' => 'Computational Thinking, Algorithms and Web Basics'],
        ];

        $subjects = [];
        foreach ($subjectsData as $s) {
            $subjects[$s['code']] = Subject::query()->firstOrCreate(
                ['code' => $s['code']],
                ['name' => $s['name'], 'description' => $s['desc']]
            );
        }

        // 6. Grading Components (default global components summing to 100)
        GradingComponent::query()->whereNull('subject_id')->delete();
        $quizComp = GradingComponent::query()->create(['name' => 'Quiz', 'weight' => 25.00]);
        $actComp = GradingComponent::query()->create(['name' => 'Activity', 'weight' => 25.00]);
        $examComp = GradingComponent::query()->create(['name' => 'Exam', 'weight' => 30.00]);
        $projComp = GradingComponent::query()->create(['name' => 'Project', 'weight' => 20.00]);

        // 7. ClassSubjects
        $classSubjects = [];
        $classSubjects[] = ClassSubject::query()->create([
            'section_id' => $sec7Jude->id,
            'subject_id' => $subjects['MATH101']->id,
            'teacher_id' => $teachers['teacher@example.com']->id,
            'schedule' => ['room' => 'Rm 201', 'days' => ['Mon', 'Wed', 'Fri'], 'time' => '08:00 - 09:30'],
        ]);
        $classSubjects[] = ClassSubject::query()->create([
            'section_id' => $sec7Jude->id,
            'subject_id' => $subjects['SCI101']->id,
            'teacher_id' => $teachers['teacher2@example.com']->id,
            'schedule' => ['room' => 'Lab 1', 'days' => ['Tue', 'Thu'], 'time' => '09:30 - 11:30'],
        ]);
        $classSubjects[] = ClassSubject::query()->create([
            'section_id' => $sec7Jude->id,
            'subject_id' => $subjects['ENG101']->id,
            'teacher_id' => $teachers['teacher3@example.com']->id,
            'schedule' => ['room' => 'Rm 201', 'days' => ['Mon', 'Wed', 'Fri'], 'time' => '10:00 - 11:00'],
        ]);
        $classSubjects[] = ClassSubject::query()->create([
            'section_id' => $sec7Jude->id,
            'subject_id' => $subjects['SOC101']->id,
            'teacher_id' => $teachers['teacher4@example.com']->id,
            'schedule' => ['room' => 'Rm 201', 'days' => ['Tue', 'Thu'], 'time' => '01:00 - 02:30'],
        ]);
        $classSubjects[] = ClassSubject::query()->create([
            'section_id' => $sec7Jude->id,
            'subject_id' => $subjects['CS101']->id,
            'teacher_id' => $teachers['teacher@example.com']->id,
            'schedule' => ['room' => 'ComLab A', 'days' => ['Wed', 'Fri'], 'time' => '02:30 - 04:00'],
        ]);

        $classSubjects[] = ClassSubject::query()->create([
            'section_id' => $sec7Thomas->id,
            'subject_id' => $subjects['MATH101']->id,
            'teacher_id' => $teachers['teacher@example.com']->id,
            'schedule' => ['room' => 'Rm 202', 'days' => ['Mon', 'Wed', 'Fri'], 'time' => '09:30 - 11:00'],
        ]);
        $classSubjects[] = ClassSubject::query()->create([
            'section_id' => $sec7Thomas->id,
            'subject_id' => $subjects['SCI101']->id,
            'teacher_id' => $teachers['teacher2@example.com']->id,
            'schedule' => ['room' => 'Lab 2', 'days' => ['Tue', 'Thu'], 'time' => '01:00 - 03:00'],
        ]);

        $classSubjects[] = ClassSubject::query()->create([
            'section_id' => $sec8Francis->id,
            'subject_id' => $subjects['ENG101']->id,
            'teacher_id' => $teachers['teacher3@example.com']->id,
            'schedule' => ['room' => 'Rm 301', 'days' => ['Mon', 'Wed', 'Fri'], 'time' => '08:00 - 09:30'],
        ]);
        $classSubjects[] = ClassSubject::query()->create([
            'section_id' => $sec8Claire->id,
            'subject_id' => $subjects['SOC101']->id,
            'teacher_id' => $teachers['teacher4@example.com']->id,
            'schedule' => ['room' => 'Rm 302', 'days' => ['Tue', 'Thu'], 'time' => '09:30 - 11:00'],
        ]);

        // 8. Students
        $studentsList = [
            // Section 7-St. Jude
            ['name' => 'Alex Cruz', 'email' => 'student@example.com', 'std_no' => 'STD-2026-001', 'sec' => $sec7Jude, 'profile' => 'high'],
            ['name' => 'Bea Alonzo', 'email' => 'bea.alonzo@example.com', 'std_no' => 'STD-2026-002', 'sec' => $sec7Jude, 'profile' => 'high'],
            ['name' => 'Christian Bautista', 'email' => 'c.bautista@example.com', 'std_no' => 'STD-2026-003', 'sec' => $sec7Jude, 'profile' => 'mid'],
            ['name' => 'Diana Zubiri', 'email' => 'd.zubiri@example.com', 'std_no' => 'STD-2026-004', 'sec' => $sec7Jude, 'profile' => 'mid'],
            ['name' => 'Eduardo Manzano', 'email' => 'e.manzano@example.com', 'std_no' => 'STD-2026-005', 'sec' => $sec7Jude, 'profile' => 'mid'],
            ['name' => 'Francis Magalona', 'email' => 'f.magalona@example.com', 'std_no' => 'STD-2026-006', 'sec' => $sec7Jude, 'profile' => 'high'],
            ['name' => 'Giselle Sanchez', 'email' => 'g.sanchez@example.com', 'std_no' => 'STD-2026-007', 'sec' => $sec7Jude, 'profile' => 'low'],
            ['name' => 'Herbert Bautista', 'email' => 'h.bautista@example.com', 'std_no' => 'STD-2026-008', 'sec' => $sec7Jude, 'profile' => 'mid'],

            // Section 7-St. Thomas
            ['name' => 'Ian Veneracion', 'email' => 'i.veneracion@example.com', 'std_no' => 'STD-2026-009', 'sec' => $sec7Thomas, 'profile' => 'high'],
            ['name' => 'Judy Ann Santos', 'email' => 'j.santos@example.com', 'std_no' => 'STD-2026-010', 'sec' => $sec7Thomas, 'profile' => 'high'],
            ['name' => 'Kaye Abad', 'email' => 'k.abad@example.com', 'std_no' => 'STD-2026-011', 'sec' => $sec7Thomas, 'profile' => 'mid'],
            ['name' => 'Luis Manzano', 'email' => 'l.manzano@example.com', 'std_no' => 'STD-2026-012', 'sec' => $sec7Thomas, 'profile' => 'low'],
            ['name' => 'Maja Salvador', 'email' => 'm.salvador@example.com', 'std_no' => 'STD-2026-013', 'sec' => $sec7Thomas, 'profile' => 'mid'],

            // Section 8-St. Francis
            ['name' => 'Nadine Lustre', 'email' => 'n.lustre@example.com', 'std_no' => 'STD-2026-014', 'sec' => $sec8Francis, 'profile' => 'high'],
            ['name' => 'Ogie Alcasid', 'email' => 'o.alcasid@example.com', 'std_no' => 'STD-2026-015', 'sec' => $sec8Francis, 'profile' => 'mid'],
            ['name' => 'Piolo Pascual', 'email' => 'p.pascual@example.com', 'std_no' => 'STD-2026-016', 'sec' => $sec8Francis, 'profile' => 'high'],
            ['name' => 'Queenie Padilla', 'email' => 'q.padilla@example.com', 'std_no' => 'STD-2026-017', 'sec' => $sec8Francis, 'profile' => 'low'],

            // Section 8-St. Claire
            ['name' => 'Regine Velasquez', 'email' => 'r.velasquez@example.com', 'std_no' => 'STD-2026-018', 'sec' => $sec8Claire, 'profile' => 'high'],
            ['name' => 'Sam Milby', 'email' => 's.milby@example.com', 'std_no' => 'STD-2026-019', 'sec' => $sec8Claire, 'profile' => 'mid'],
            ['name' => 'Toni Gonzaga', 'email' => 't.gonzaga@example.com', 'std_no' => 'STD-2026-020', 'sec' => $sec8Claire, 'profile' => 'mid'],
        ];

        $students = [];
        $studentProfiles = [];

        foreach ($studentsList as $sData) {
            $u = User::query()->firstOrCreate(
                ['email' => $sData['email']],
                [
                    'name' => $sData['name'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'must_change_password' => false,
                ]
            );

            if (! $u->hasRole($studentRole)) {
                $u->assignRole($studentRole);
            }

            $st = Student::query()->firstOrCreate(
                ['student_no' => $sData['std_no']],
                [
                    'user_id' => $u->id,
                    'section_id' => $sData['sec']->id,
                    'birthdate' => '2013-08-20',
                    'guardian_name' => 'Parent of '.$sData['name'],
                    'guardian_contact' => '+63 917 111 2233',
                    'status' => StudentStatus::Active,
                ]
            );

            $students[] = $st;
            $studentProfiles[$st->id] = $sData['profile'];
        }

        // 9. Create Activities for Periods Q1, Q2, Q3
        $periods = [$q1, $q2, $q3];

        Activity::withoutEvents(function () use ($classSubjects, $periods, $quizComp, $actComp, $projComp, $examComp, $studentProfiles, $q3) {
            Score::withoutEvents(function () use ($classSubjects, $periods, $quizComp, $actComp, $projComp, $examComp, $studentProfiles, $q3) {
                Attendance::withoutEvents(function () use ($classSubjects, $periods, $quizComp, $actComp, $projComp, $examComp, $studentProfiles, $q3) {
                    foreach ($classSubjects as $cs) {
                        $secStudents = Student::query()->where('section_id', $cs->section_id)->get();

                        foreach ($periods as $period) {
                            $activitiesDef = [
                                ['title' => 'Diagnostic Quiz', 'comp' => $quizComp, 'type' => ActivityType::Quiz, 'max' => 20.0],
                                ['title' => 'Mastery Quiz', 'comp' => $quizComp, 'type' => ActivityType::Quiz, 'max' => 25.0],
                                ['title' => 'Lab Worksheet', 'comp' => $actComp, 'type' => ActivityType::Assignment, 'max' => 50.0],
                                ['title' => 'Group Project', 'comp' => $projComp, 'type' => ActivityType::Project, 'max' => 100.0],
                                ['title' => 'Quarterly Exam', 'comp' => $examComp, 'type' => ActivityType::Exam, 'max' => 100.0],
                            ];

                            // For Q3 (active period), only generate first 3 activities so grading is in-progress
                            if ($period->id === $q3->id) {
                                $activitiesDef = array_slice($activitiesDef, 0, 3);
                            }

                            foreach ($activitiesDef as $aDef) {
                                $activity = Activity::query()->create([
                                    'class_subject_id' => $cs->id,
                                    'grading_period_id' => $period->id,
                                    'grading_component_id' => $aDef['comp']->id,
                                    'title' => $aDef['title'].' ('.$period->name.')',
                                    'type' => $aDef['type'],
                                    'description' => 'Evaluated assessment for '.$aDef['title'],
                                    'max_score' => $aDef['max'],
                                    'due_date' => $period->starts_on->addDays(14),
                                ]);

                                // Generate scores for each enrolled student
                                foreach ($secStudents as $student) {
                                    $profile = $studentProfiles[$student->id] ?? 'mid';

                                    if ($profile === 'high') {
                                        $earned = round($aDef['max'] * (mt_rand(90, 98) / 100), 1);
                                        $status = ScoreStatus::Graded;
                                    } elseif ($profile === 'mid') {
                                        $earned = round($aDef['max'] * (mt_rand(78, 88) / 100), 1);
                                        $status = ScoreStatus::Graded;
                                    } else { // low / at-risk
                                        $isMissing = mt_rand(1, 10) > 7;
                                        if ($isMissing) {
                                            $earned = null;
                                            $status = ScoreStatus::Missing;
                                        } else {
                                            $earned = round($aDef['max'] * (mt_rand(55, 72) / 100), 1);
                                            $status = ScoreStatus::Graded;
                                        }
                                    }

                                    Score::query()->create([
                                        'activity_id' => $activity->id,
                                        'student_id' => $student->id,
                                        'score' => $earned,
                                        'status' => $status,
                                        'remarks' => $status === ScoreStatus::Missing ? 'Did not submit' : null,
                                        'graded_by' => $cs->teacher->user_id,
                                    ]);
                                }
                            }

                            // Generate Attendance records for students
                            $dates = [
                                $period->starts_on->addDays(3)->format('Y-m-d'),
                                $period->starts_on->addDays(10)->format('Y-m-d'),
                                $period->starts_on->addDays(17)->format('Y-m-d'),
                                $period->starts_on->addDays(24)->format('Y-m-d'),
                                $period->starts_on->addDays(31)->format('Y-m-d'),
                            ];

                            foreach ($dates as $date) {
                                foreach ($secStudents as $student) {
                                    $profile = $studentProfiles[$student->id] ?? 'mid';
                                    $status = match ($profile) {
                                        'high' => AttendanceStatus::Present,
                                        'mid' => mt_rand(1, 10) > 8 ? AttendanceStatus::Late : AttendanceStatus::Present,
                                        default => mt_rand(1, 10) > 6 ? AttendanceStatus::Absent : (mt_rand(1, 10) > 7 ? AttendanceStatus::Late : AttendanceStatus::Present),
                                    };

                                    Attendance::query()->firstOrCreate(
                                        [
                                            'class_subject_id' => $cs->id,
                                            'student_id' => $student->id,
                                            'date' => $date,
                                        ],
                                        ['status' => $status]
                                    );
                                }
                            }
                        }
                    }
                });
            });
        });

        // 10. Recalculate Grades and Performance Summaries
        $recalculator = app(RecalculateStudentGrade::class);
        $evaluator = app(PerformanceEvaluator::class);

        foreach ($classSubjects as $cs) {
            $secStudents = Student::query()->where('section_id', $cs->section_id)->get();
            foreach ($periods as $period) {
                foreach ($secStudents as $student) {
                    $recalculator($student->id, $cs->id, $period->id);
                }
            }
        }

        foreach ($students as $student) {
            foreach ($periods as $period) {
                $evaluator->evaluate($student, $period);
            }
        }

        // 11. Finalize Historical Periods: Close Q1 and Q2
        $q1->update(['status' => PeriodStatus::Closed]);
        $q2->update(['status' => PeriodStatus::Closed]);
    }
}
