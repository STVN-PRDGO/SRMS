<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ActivityType: string implements HasLabel
{
    case Quiz = 'quiz';
    case Assignment = 'assignment';
    case Project = 'project';
    case Exam = 'exam';
    case Participation = 'participation';

    public function getLabel(): string
    {
        return str($this->value)->headline()->toString();
    }
}
