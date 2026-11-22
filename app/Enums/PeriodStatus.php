<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PeriodStatus: string implements HasColor, HasIcon, HasLabel
{
    case Draft = 'draft';
    case Open = 'open';
    case Closed = 'closed';

    public function getLabel(): string
    {
        return str($this->value)->headline()->toString();
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Open => 'success',
            self::Closed => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Draft => 'heroicon-m-pencil-square',
            self::Open => 'heroicon-m-lock-open',
            self::Closed => 'heroicon-m-lock-closed',
        };
    }
}
