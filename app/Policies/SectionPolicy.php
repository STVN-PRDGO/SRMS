<?php

namespace App\Policies;

class SectionPolicy extends PanelPolicy
{
    protected function subject(): string
    {
        return 'section';
    }
}
