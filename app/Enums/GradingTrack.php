<?php

namespace App\Enums;

use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;

enum GradingTrack: string implements HasDescription, HasLabel
{
    case HighSchool = 'high_school';
    case College = 'college';

    public function getLabel(): string
    {
        return match ($this) {
            self::HighSchool => 'High school (DepEd)',
            self::College => 'College',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::HighSchool => 'Quizzes 20, activities 20, projects 40, midterm 10, final 10. The weighted score is transmuted to a quarterly grade. Remarks follow DepEd descriptors, with 75 as the passing grade.',
            self::College => 'Quizzes 20, activities 15, projects 15, midterm 25, final 25. The percentage stays as computed and converts to the 1.00–5.00 scale. 3.00 is the lowest passing grade.',
        };
    }
}
