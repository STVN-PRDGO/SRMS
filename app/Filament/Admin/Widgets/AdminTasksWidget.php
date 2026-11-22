<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Pages\SectionAverages;
use App\Filament\Admin\Resources\ClassSubjectResource;
use App\Filament\Admin\Resources\GradingPeriodResource;
use App\Filament\Admin\Resources\StudentResource;
use Filament\Widgets\Widget;

class AdminTasksWidget extends Widget
{
    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.admin.widgets.tasks';

    public static function canView(): bool
    {
        return count(static::links()) > 0;
    }

    /**
     * @return list<array{label: string, hint: string, url: string}>
     */
    public static function links(): array
    {
        if (! auth()->check()) {
            return [];
        }

        $links = [];

        if (StudentResource::canViewAny()) {
            $links[] = [
                'label' => 'Students',
                'hint' => 'Enrollment and records',
                'url' => StudentResource::getUrl(),
            ];
        }

        if (ClassSubjectResource::canViewAny()) {
            $links[] = [
                'label' => 'Classes',
                'hint' => 'Section and subject assignments',
                'url' => ClassSubjectResource::getUrl(),
            ];
        }

        if (GradingPeriodResource::canViewAny()) {
            $links[] = [
                'label' => 'Periods',
                'hint' => 'Open, close, or reopen a term',
                'url' => GradingPeriodResource::getUrl(),
            ];
        }

        if (SectionAverages::canAccess()) {
            $links[] = [
                'label' => 'Reports',
                'hint' => 'Section averages',
                'url' => SectionAverages::getUrl(),
            ];
        }

        return $links;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'links' => static::links(),
        ];
    }
}
