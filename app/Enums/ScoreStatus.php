<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ScoreStatus: string implements HasColor, HasIcon, HasLabel
{
    case Graded = 'graded';
    case Missing = 'missing';
    case Excused = 'excused';

    public function getLabel(): string
    {
        return str($this->value)->headline()->toString();
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Graded => 'success',
            self::Missing => 'danger',
            self::Excused => 'warning',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Graded => 'heroicon-m-check-badge',
            self::Missing => 'heroicon-m-exclamation-circle',
            self::Excused => 'heroicon-m-minus-circle',
        };
    }
}
