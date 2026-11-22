<?php

namespace App\Policies;

class TeacherPolicy extends PanelPolicy
{
    protected function subject(): string
    {
        return 'teacher';
    }
}
