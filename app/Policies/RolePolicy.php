<?php

namespace App\Policies;

class RolePolicy extends PanelPolicy
{
    protected function subject(): string
    {
        return 'role';
    }

    protected function panel(): string
    {
        return 'admin';
    }
}
