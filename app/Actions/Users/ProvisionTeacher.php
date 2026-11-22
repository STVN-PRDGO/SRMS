<?php

namespace App\Actions\Users;

use App\Models\Teacher;
use Illuminate\Support\Facades\DB;

class ProvisionTeacher
{
    public function __construct(private readonly CreateAccount $createAccount) {}

    /**
     * @param  array{name: string, email: string, password?: string, employee_no: string, department?: ?string, contact_no?: ?string}  $data
     */
    public function __invoke(array $data): Teacher
    {
        return DB::transaction(function () use ($data): Teacher {
            $user = ($this->createAccount)($data, 'teacher');

            return Teacher::query()->create([
                'user_id' => $user->id,
                'employee_no' => $data['employee_no'],
                'department' => $data['department'] ?? null,
                'contact_no' => $data['contact_no'] ?? null,
            ]);
        });
    }
}
