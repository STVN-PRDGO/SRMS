<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum Trend: string implements HasColor, HasIcon, HasLabel
{
    case Up = 'up';
    case Same = 'same';
    case Down = 'down';

    public function getLabel(): string
    {
        return str($this->value)->headline()->toString();
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Up => 'success',
            self::Same => 'gray',
            self::Down => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Up => 'heroicon-m-arrow-trending-up',
            self::Same => 'heroicon-m-minus',
            self::Down => 'heroicon-m-arrow-trending-down',
        };
    }
}
