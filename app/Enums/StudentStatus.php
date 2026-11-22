<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum StudentStatus: string implements HasLabel
{
    case Active = 'active';
    case Transferred = 'transferred';
    case Graduated = 'graduated';

    public function getLabel(): string
    {
        return str($this->value)->headline()->toString();
    }
}
