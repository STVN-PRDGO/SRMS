<?php

namespace App\Policies;

class AuditPolicy extends PanelPolicy
{
    protected function subject(): string
    {
        return 'audit';
    }

    protected function panel(): string
    {
        return 'admin';
    }
}
