<?php

namespace App\Policies;

class SchoolYearPolicy extends PanelPolicy
{
    protected function subject(): string
    {
        return 'school_year';
    }
}
