<?php

namespace App\Filament\Teacher\Widgets;

use App\Models\ClassSubject;
use App\Models\PerformanceLevel;
use App\Models\PerformanceSummary;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AtRiskWidget extends StatsOverviewWidget
{
    use HasWidgetShield;

    protected int|string|array $columnSpan = 1;

    protected int|array|null $columns = 1;

    protected function getStats(): array
    {
        $bottom = PerformanceLevel::query()->orderBy('min_grade')->first();
        $sectionIds = ClassSubject::query()->visibleTo(auth()->user())->pluck('section_id');
        $count = 0;

        if ($bottom !== null) {
            $count = PerformanceSummary::query()
                ->where('performance_level_id', $bottom->id)
                ->whereHas('student', fn ($query) => $query->whereIn('section_id', $sectionIds))
                ->count();
        }

        return [
            Stat::make('At-risk students', (string) $count)
                ->description($bottom === null ? 'No performance levels set' : $bottom->name)
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }
}
