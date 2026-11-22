<?php

namespace App\Policies;

class UserPolicy extends PanelPolicy
{
    protected function subject(): string
    {
        return 'user';
    }
}
