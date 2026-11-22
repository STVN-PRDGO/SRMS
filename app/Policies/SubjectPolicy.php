<?php

namespace App\Policies;

class SubjectPolicy extends PanelPolicy
{
    protected function subject(): string
    {
        return 'subject';
    }
}
