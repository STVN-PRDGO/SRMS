<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Filament\Teacher\Resources\AttendanceResource\Pages\ManageAttendances;
use App\Models\Attendance;
use Database\Seeders\RoleAndPermissionSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Support\BuildsSchool;
use Tests\TestCase;

class AttendanceRosterTest extends TestCase
{
    use BuildsSchool;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        Filament::setCurrentPanel(Filament::getPanel('teacher'));
    }

    public function test_teacher_saves_one_roster_instead_of_editing_each_row(): void
    {
        $school = $this->school();
        $teacher = $school['teacher']->user;
        $teacher->assignRole('teacher');

        Livewire::actingAs($teacher)
            ->test(ManageAttendances::class)
            ->set('data.class_subject_id', $school['classSubject']->id)
            ->set('data.date', '2026-06-15')
            ->call('loadRows')
            ->set('data.rows.0.status', AttendanceStatus::Absent->value)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas(Attendance::class, [
            'class_subject_id' => $school['classSubject']->id,
            'student_id' => $school['student']->id,
            'status' => AttendanceStatus::Absent->value,
        ]);
    }
}
