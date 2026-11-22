<?php

namespace App\Filament\Admin;

use Filament\Support\Contracts\Collapsible;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum AdminNavigationGroup: string implements Collapsible, HasIcon, HasLabel
{
    case School = 'School';
    case Classes = 'Classes';
    case People = 'People';
    case Reports = 'Reports';
    case Grading = 'Grading';
    case Access = 'Access';

    public function getLabel(): string
    {
        return $this->value;
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::School => Heroicon::OutlinedBuildingLibrary,
            self::Classes => Heroicon::OutlinedBookOpen,
            self::People => Heroicon::OutlinedUserGroup,
            self::Reports => Heroicon::OutlinedChartBar,
            self::Grading => Heroicon::OutlinedScale,
            self::Access => Heroicon::OutlinedKey,
        };
    }

    public function isCollapsible(): bool
    {
        return true;
    }

    public function isCollapsed(): bool
    {
        return match ($this) {
            self::Grading, self::Access => true,
            default => false,
        };
    }
}
