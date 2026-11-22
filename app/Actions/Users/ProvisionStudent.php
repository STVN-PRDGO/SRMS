<?php

namespace App\Actions\Users;

use App\Enums\StudentStatus;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class ProvisionStudent
{
    public function __construct(private readonly CreateAccount $createAccount) {}

    /**
     * @param  array{name: string, email: string, password?: string, student_no: string, section_id: int, birthdate?: ?string, guardian_name?: ?string, guardian_contact?: ?string, status?: string}  $data
     */
    public function __invoke(array $data): Student
    {
        return DB::transaction(function () use ($data): Student {
            $user = ($this->createAccount)($data, 'student');

            return Student::query()->create([
                'user_id' => $user->id,
                'student_no' => $data['student_no'],
                'section_id' => $data['section_id'],
                'birthdate' => $data['birthdate'] ?? null,
                'guardian_name' => $data['guardian_name'] ?? null,
                'guardian_contact' => $data['guardian_contact'] ?? null,
                'status' => $data['status'] ?? StudentStatus::Active,
            ]);
        });
    }
}
