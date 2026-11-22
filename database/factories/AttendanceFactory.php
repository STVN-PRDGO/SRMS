<?php

namespace Database\Factories;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\ClassSubject;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        return [
            'class_subject_id' => ClassSubject::factory(),
            'student_id' => Student::factory(),
            'date' => '2026-06-15',
            'status' => AttendanceStatus::Present,
            'remarks' => null,
        ];
    }
}
