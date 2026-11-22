<?php

namespace App\Policies;

use App\Models\Grade;
use App\Models\User;
use App\Support\StudentContext;

class GradePolicy extends PanelPolicy
{
    protected function subject(): string
    {
        return 'grade';
    }

    public function view(User $user, mixed $record): bool
    {
        if (! parent::view($user, $record) || ! $record instanceof Grade) {
            return false;
        }

        if ($this->panel() === 'student') {
            return $record->student_id === StudentContext::id($user);
        }

        return true;
    }
}
