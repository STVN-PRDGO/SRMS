<?php

namespace App\Filament\Teacher\Widgets;

use App\Models\ClassSubject;
use App\Models\Grade;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ClassAverageWidget extends StatsOverviewWidget
{
    use HasWidgetShield;

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 1;

    protected int|array|null $columns = 1;

    protected function getStats(): array
    {
        $average = Grade::query()
            ->whereIn('class_subject_id', ClassSubject::query()->visibleTo(auth()->user())->pluck('id'))
            ->avg('raw_grade');

        return [
            Stat::make('Class average', $average === null ? '—' : number_format((float) $average, 2))
                ->description('Across your classes')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),
        ];
    }
}
