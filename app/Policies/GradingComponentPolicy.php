<?php

namespace App\Policies;

class GradingComponentPolicy extends PanelPolicy
{
    protected function subject(): string
    {
        return 'grading_component';
    }
}
