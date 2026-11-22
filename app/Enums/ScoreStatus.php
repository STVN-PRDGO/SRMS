<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ScoreStatus: string implements HasLabel
{
    case Graded = 'graded';
    case Missing = 'missing';
    case Excused = 'excused';

    public function getLabel(): string
    {
        return str($this->value)->headline()->toString();
    }
}
