<?php

namespace App\Actions\Periods;

use App\Enums\PeriodStatus;
use App\Models\GradingPeriod;
use App\Models\User;
use App\Support\PermissionName;
use Illuminate\Validation\ValidationException;

class OpenPeriod
{
    public function __invoke(GradingPeriod $period, User $user): void
    {
        if (! $user->can(PermissionName::for('update', 'grading_period', 'admin'))) {
            throw ValidationException::withMessages([
                'period' => 'You cannot open this grading period.',
            ]);
        }

        if ($period->status !== PeriodStatus::Draft) {
            throw ValidationException::withMessages([
                'period' => 'Only a draft grading period can be opened.',
            ]);
        }

        $period->update(['status' => PeriodStatus::Open]);
    }
}
