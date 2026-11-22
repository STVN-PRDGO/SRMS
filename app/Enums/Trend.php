<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Trend: string implements HasLabel
{
    case Up = 'up';
    case Same = 'same';
    case Down = 'down';

    public function getLabel(): string
    {
        return str($this->value)->headline()->toString();
    }
}
