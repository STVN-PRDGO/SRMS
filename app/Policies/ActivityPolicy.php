<?php

namespace App\Policies;

use App\Models\Activity;
use App\Models\User;
use App\Support\StudentContext;

class ActivityPolicy extends PanelPolicy
{
    protected function subject(): string
    {
        return 'activity';
    }

    public function update(User $user, mixed $record): bool
    {
        return parent::update($user, $record) && $this->teacherMayChange($user, $record);
    }

    public function delete(User $user, mixed $record): bool
    {
        return parent::delete($user, $record) && $this->teacherMayChange($user, $record);
    }

    public function view(User $user, mixed $record): bool
    {
        if (! parent::view($user, $record)) {
            return false;
        }

        if ($this->panel() === 'teacher' && $record instanceof Activity) {
            return $record->classSubject->isManageableBy($user);
        }

        if ($this->panel() === 'student' && $record instanceof Activity) {
            return StudentContext::id($user) === $user->student?->id
                ? $record->classSubject->section_id === $user->student->section_id
                : $record->classSubject->section->students()->whereKey(StudentContext::id($user))->exists();
        }

        return true;
    }

    private function teacherMayChange(User $user, mixed $record): bool
    {
        if ($this->panel() !== 'teacher' || ! $record instanceof Activity) {
            return $this->panel() !== 'student';
        }

        return $record->classSubject->isManageableBy($user) && ! $record->gradingPeriod->isClosed();
    }
}
