<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum StudentStatus: string implements HasColor, HasIcon, HasLabel
{
    case Active = 'active';
    case Transferred = 'transferred';
    case Graduated = 'graduated';

    public function getLabel(): string
    {
        return str($this->value)->headline()->toString();
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Active => 'success',
            self::Transferred => 'warning',
            self::Graduated => 'info',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Active => 'heroicon-m-check',
            self::Transferred => 'heroicon-m-arrow-right-start-on-rectangle',
            self::Graduated => 'heroicon-m-academic-cap',
        };
    }
}
