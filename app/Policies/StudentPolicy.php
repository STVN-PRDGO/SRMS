<?php

namespace App\Policies;

class StudentPolicy extends PanelPolicy
{
    protected function subject(): string
    {
        return 'student';
    }
}
