<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum AttendanceStatus: string implements HasColor, HasIcon, HasLabel
{
    case Present = 'present';
    case Late = 'late';
    case Absent = 'absent';
    case Excused = 'excused';

    public function getLabel(): string
    {
        return str($this->value)->headline()->toString();
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Present => 'success',
            self::Late => 'warning',
            self::Absent => 'danger',
            self::Excused => 'info',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Present => 'heroicon-m-check-circle',
            self::Late => 'heroicon-m-clock',
            self::Absent => 'heroicon-m-x-circle',
            self::Excused => 'heroicon-m-shield-check',
        };
    }
}
