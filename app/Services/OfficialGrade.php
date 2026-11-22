<?php

namespace App\Services;

use App\Enums\GradingTrack;
use App\Support\PhilippineGrading;

class OfficialGrade
{
    /**
     * @return array{raw_grade: float, letter: ?string, remark: ?string}
     */
    public function fromInitial(GradingTrack $track, float $initial): array
    {
        $grade = $track === GradingTrack::HighSchool
            ? PhilippineGrading::quarterlyGrade($initial)
            : round($initial, 2, PHP_ROUND_HALF_UP);

        $mark = PhilippineGrading::mark($track, $grade);

        return [
            'raw_grade' => $grade,
            'letter' => $mark['letter'],
            'remark' => $mark['remark'],
        ];
    }
}
