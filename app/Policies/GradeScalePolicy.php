<?php

namespace App\Policies;

class GradeScalePolicy extends PanelPolicy
{
    protected function subject(): string
    {
        return 'grade_scale';
    }
}
