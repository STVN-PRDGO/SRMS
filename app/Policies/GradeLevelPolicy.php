<?php

namespace App\Policies;

class GradeLevelPolicy extends PanelPolicy
{
    protected function subject(): string
    {
        return 'grade_level';
    }
}
