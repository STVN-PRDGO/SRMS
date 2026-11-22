<?php

namespace App\Support;

use Filament\Facades\Filament;
use Filament\Widgets\WidgetConfiguration;
use Illuminate\Support\Str;

class PermissionName
{
    public static function for(string $affix, string $subject, string $panel): string
    {
        return Str::snake($affix).'_'.Str::snake($subject).'_'.$panel;
    }

    public static function panelForEntity(string $entity): string
    {
        foreach (Filament::getPanels() as $id => $panel) {
            foreach ([...$panel->getResources(), ...array_values($panel->getPages()), ...$panel->getWidgets()] as $candidate) {
                $class = $candidate instanceof WidgetConfiguration ? $candidate->widget : $candidate;

                if ($class === $entity) {
                    return $id;
                }
            }
        }

        return 'admin';
    }
}
