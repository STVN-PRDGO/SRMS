<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\GradingPeriod;
use App\Models\User;
use App\Support\StudentContext;

class AttendancePolicy extends PanelPolicy
{
    protected function subject(): string
    {
        return 'attendance';
    }

    public function view(User $user, mixed $record): bool
    {
        if (! parent::view($user, $record) || ! $record instanceof Attendance) {
            return false;
        }

        if ($this->panel() === 'teacher') {
            return $record->classSubject->isManageableBy($user);
        }

        if ($this->panel() === 'student') {
            return $record->student_id === StudentContext::id($user);
        }

        return true;
    }

    public function update(User $user, mixed $record): bool
    {
        return $this->teacherMayChange($user, $record) && parent::update($user, $record);
    }

    public function delete(User $user, mixed $record): bool
    {
        return $this->teacherMayChange($user, $record) && parent::delete($user, $record);
    }

    private function teacherMayChange(User $user, mixed $record): bool
    {
        if (! $record instanceof Attendance) {
            return false;
        }

        if ($this->panel() !== 'teacher') {
            return false;
        }

        $record->loadMissing('classSubject.section');
        $open = GradingPeriod::query()
            ->where('school_year_id', $record->classSubject->section->school_year_id)
            ->whereDate('starts_on', '<=', $record->date)
            ->whereDate('ends_on', '>=', $record->date)
            ->where('status', 'open')
            ->exists();

        return $open && $record->classSubject->isManageableBy($user);
    }
}
