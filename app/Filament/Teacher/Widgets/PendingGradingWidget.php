<?php

namespace App\Filament\Teacher\Widgets;

use App\Enums\PeriodStatus;
use App\Models\Activity;
use App\Models\ClassSubject;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PendingGradingWidget extends StatsOverviewWidget
{
    use HasWidgetShield;

    protected int|string|array $columnSpan = 1;

    protected int|array|null $columns = 1;

    protected function getStats(): array
    {
        $classIds = ClassSubject::query()->visibleTo(auth()->user())->pluck('id');
        $pending = Activity::query()
            ->whereIn('class_subject_id', $classIds)
            ->whereHas('gradingPeriod', fn ($query) => $query->where('status', PeriodStatus::Open))
            ->withCount('scores')
            ->with(['classSubject.section' => fn ($query) => $query->withCount('students')])
            ->get()
            ->filter(function (Activity $activity): bool {
                $roster = $activity->classSubject->section->students_count ?? 0;

                return $activity->scores_count < $roster;
            })
            ->count();

        return [
            Stat::make('Activities still open', (string) $pending)
                ->description('Open period, roster not fully scored')
                ->descriptionIcon('heroicon-m-pencil-square')
                ->color('warning'),
        ];
    }
}
