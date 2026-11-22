<?php

namespace App\Actions\Periods;

use App\Models\GradingPeriod;
use Illuminate\Validation\ValidationException;

class AssertPeriodDoesNotOverlap
{
    public function __invoke(int $schoolYearId, string $startsOn, string $endsOn, ?int $ignoreId = null): void
    {
        if ($startsOn > $endsOn) {
            throw ValidationException::withMessages([
                'ends_on' => 'The period must end on or after the day it starts.',
            ]);
        }

        $overlap = GradingPeriod::query()
            ->where('school_year_id', $schoolYearId)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->whereDate('starts_on', '<=', $endsOn)
            ->whereDate('ends_on', '>=', $startsOn)
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'starts_on' => 'Grading periods in the same school year cannot overlap.',
            ]);
        }
    }
}
