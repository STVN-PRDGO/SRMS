<?php

namespace App\Policies;

use App\Models\Score;
use App\Models\User;
use App\Support\StudentContext;

class ScorePolicy extends PanelPolicy
{
    protected function subject(): string
    {
        return 'score';
    }

    public function view(User $user, mixed $record): bool
    {
        if (! parent::view($user, $record) || ! $record instanceof Score) {
            return false;
        }

        if ($this->panel() === 'teacher') {
            return $record->activity->classSubject->isManageableBy($user);
        }

        if ($this->panel() === 'student') {
            return $record->student_id === StudentContext::id($user);
        }

        return true;
    }

    public function update(User $user, mixed $record): bool
    {
        return parent::update($user, $record) && $this->unlocked($user, $record);
    }

    public function delete(User $user, mixed $record): bool
    {
        return parent::delete($user, $record) && $this->unlocked($user, $record);
    }

    private function unlocked(User $user, mixed $record): bool
    {
        if ($this->panel() !== 'teacher' || ! $record instanceof Score) {
            return false;
        }

        $record->loadMissing('activity.gradingPeriod', 'activity.classSubject');

        return $record->activity->classSubject->isManageableBy($user)
            && $record->activity->gradingPeriod->acceptsEntries();
    }
}
