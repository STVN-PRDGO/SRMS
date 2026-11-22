<?php

namespace App\Support;

use App\Models\Student;
use App\Models\User;

class StudentContext
{
    public static function id(?User $user = null): ?int
    {
        $user ??= auth()->user();

        if (! $user instanceof User) {
            return null;
        }

        if ($user->student !== null) {
            return $user->student->id;
        }

        if (! $user->can('view_as_student')) {
            return null;
        }

        $selected = (int) session('viewing_student_id');

        if ($selected === 0 || ! Student::query()->whereKey($selected)->exists()) {
            return null;
        }

        return $selected;
    }
}
