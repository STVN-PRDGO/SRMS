<?php

namespace App\Filament\Teacher\Concerns;

use App\Models\ClassSubject;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait ResolvesTaughtStudents
{
    protected function taughtStudentsQuery(User $user): Builder
    {
        return Student::query()
            ->whereHas('section.classSubjects', fn (Builder $query) => $query->visibleTo($user));
    }

    protected function teachesStudent(User $user, Student $student): bool
    {
        return ClassSubject::query()
            ->visibleTo($user)
            ->where('section_id', $student->section_id)
            ->exists();
    }
}
