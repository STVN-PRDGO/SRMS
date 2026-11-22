<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PeriodStatus: string implements HasLabel
{
    case Draft = 'draft';
    case Open = 'open';
    case Closed = 'closed';

    public function getLabel(): string
    {
        return str($this->value)->headline()->toString();
    }
}
