<?php

namespace App\Policies;

use App\Models\PerformanceSummary;
use App\Models\User;
use App\Support\StudentContext;

class PerformanceSummaryPolicy extends PanelPolicy
{
    protected function subject(): string
    {
        return 'performance_summary';
    }

    public function view(User $user, mixed $record): bool
    {
        if (! parent::view($user, $record) || ! $record instanceof PerformanceSummary) {
            return false;
        }

        if ($this->panel() === 'student') {
            return $record->student_id === StudentContext::id($user);
        }

        return true;
    }
}
