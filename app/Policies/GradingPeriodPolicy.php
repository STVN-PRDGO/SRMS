<?php

namespace App\Policies;

class GradingPeriodPolicy extends PanelPolicy
{
    protected function subject(): string
    {
        return 'grading_period';
    }
}
