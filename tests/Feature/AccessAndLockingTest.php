<?php

namespace Tests\Feature;

use App\Actions\Scores\SaveScores;
use App\Enums\PeriodStatus;
use App\Enums\ScoreStatus;
use App\Filament\Student\Resources\ActivityResource;
use App\Filament\Student\Resources\GradeResource;
use App\Filament\Teacher\Resources\ClassSubjectResource;
use App\Models\Grade;
use App\Models\Score;
use App\Models\User;
use Database\Seeders\ReferenceDataSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\Support\BuildsSchool;
use Tests\TestCase;

class AccessAndLockingTest extends TestCase
{
    use BuildsSchool;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_each_role_reaches_only_the_panels_it_is_allowed_to_open(): void
    {
        $admin = $this->userWithRole('admin');
        $teacher = $this->userWithRole('teacher');
        $student = $this->userWithRole('student');

        $this->signIn($student)->get('/student')->assertOk();
        $this->signIn($student)->get('/teacher')->assertForbidden();

        $this->signIn($teacher)->get('/teacher')->assertOk();
        $this->signIn($teacher)->get('/admin')->assertForbidden();
        $this->signIn($teacher)->get('/student')->assertForbidden();

        $this->signIn($admin)->get('/admin')->assertOk();
        $this->signIn($admin)->get('/teacher')->assertOk();
        $this->signIn($admin)->get('/student')->assertOk();
    }

    public function test_a_teacher_cannot_see_another_teachers_class(): void
    {
        $school = $this->school();
        $other = $this->school();
        $teacher = $school['teacher']->user;
        $teacher->assignRole('teacher');

        Filament::setCurrentPanel(Filament::getPanel('teacher'));
        $this->actingAs($teacher);

        $ids = ClassSubjectResource::getEloquentQuery()->pluck('id');

        $this->assertTrue($ids->contains($school['classSubject']->id));
        $this->assertFalse($ids->contains($other['classSubject']->id));
        $this->assertFalse($teacher->can('view', $other['classSubject']));
    }

    public function test_a_student_cannot_see_another_students_grades(): void
    {
        $school = $this->school();
        $student = $school['student']->user;
        $student->assignRole('student');
        $other = $this->school()['student'];

        $own = Grade::query()->create([
            'student_id' => $school['student']->id,
            'class_subject_id' => $school['classSubject']->id,
            'grading_period_id' => $school['period']->id,
            'raw_grade' => 90,
        ]);
        $foreign = Grade::query()->create([
            'student_id' => $other->id,
            'class_subject_id' => $school['classSubject']->id,
            'grading_period_id' => $school['period']->id,
            'raw_grade' => 70,
        ]);

        Filament::setCurrentPanel(Filament::getPanel('student'));
        $this->actingAs($student);

        $ids = GradeResource::getEloquentQuery()->pluck('id');

        $this->assertTrue($ids->contains($own->id));
        $this->assertFalse($ids->contains($foreign->id));
        $this->assertFalse($student->can('view', $foreign));
    }

    public function test_a_closed_period_rejects_score_edits(): void
    {
        $school = $this->school();
        $teacher = $school['teacher']->user;
        $teacher->assignRole('teacher');
        $activity = $this->activity($school);
        $school['period']->update(['status' => PeriodStatus::Closed]);

        Filament::setCurrentPanel(Filament::getPanel('teacher'));
        $this->actingAs($teacher);

        $score = Score::query()->create([
            'activity_id' => $activity->id,
            'student_id' => $school['student']->id,
            'status' => ScoreStatus::Graded,
            'score' => 10,
        ]);

        $this->assertFalse($teacher->can('update', $score->fresh()));

        $this->expectException(ValidationException::class);
        app(SaveScores::class)($activity->fresh(), [[
            'student_id' => $school['student']->id,
            'score' => 8,
            'status' => 'graded',
        ]], $teacher);
    }

    public function test_permission_changes_apply_immediately_and_survive_a_seeder_rerun(): void
    {
        $student = $this->userWithRole('student');
        $role = Role::findByName('student');
        $role->revokePermissionTo('view_any_activity_student');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Filament::setCurrentPanel(Filament::getPanel('student'));
        $this->actingAs($student->fresh());
        $this->assertFalse(ActivityResource::canViewAny());

        $role->givePermissionTo('view_any_activity_student');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($student->fresh());
        $this->assertTrue(ActivityResource::canViewAny());

        $admin = $this->userWithRole('admin');
        Role::findByName('admin')->revokePermissionTo('access_teacher_panel');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($admin->fresh())->get('/teacher')->assertForbidden();

        $adminRole = Role::findByName('admin');
        $this->assertFalse($adminRole->hasPermissionTo('create_grade_student'));
        Filament::setCurrentPanel(Filament::getPanel('student'));
        $this->assertFalse($admin->fresh()->can('create', Grade::class));
        $adminRole->givePermissionTo('create_grade_student');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->assertTrue($admin->fresh()->can('create', Grade::class));

        $teacherRole = Role::findByName('teacher');
        $teacherRole->revokePermissionTo('update_score_teacher');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->assertFalse($teacherRole->fresh()->hasPermissionTo('update_score_teacher'));
    }

    public function test_view_all_classes_lets_a_teacher_see_every_class(): void
    {
        $school = $this->school();
        $other = $this->school();
        $teacher = $school['teacher']->user;
        $teacher->assignRole('teacher');

        Filament::setCurrentPanel(Filament::getPanel('teacher'));
        $this->actingAs($teacher);
        $this->assertFalse(ClassSubjectResource::getEloquentQuery()->pluck('id')->contains($other['classSubject']->id));

        Role::findByName('teacher')->givePermissionTo('view_all_classes');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($teacher->fresh());
        $this->assertTrue(ClassSubjectResource::getEloquentQuery()->pluck('id')->contains($other['classSubject']->id));
    }

    private function signIn(User $user): static
    {
        $this->flushSession();

        return $this->actingAs($user);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
