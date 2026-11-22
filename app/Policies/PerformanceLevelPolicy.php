<?php

namespace App\Policies;

class PerformanceLevelPolicy extends PanelPolicy
{
    protected function subject(): string
    {
        return 'performance_level';
    }
}
