<?php

namespace App\Filament\Student\Widgets;

use App\Models\PerformanceSummary;
use App\Support\StudentContext;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;

class PerformanceTrendWidget extends ChartWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected ?string $heading = 'Quarterly Average Progression';

    protected ?string $description = 'Your general average trend across grading periods.';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $rows = PerformanceSummary::query()
            ->where('student_id', StudentContext::id() ?? 0)
            ->with('gradingPeriod')
            ->get()
            ->sortBy(fn (PerformanceSummary $summary) => $summary->gradingPeriod->starts_on);

        return [
            'datasets' => [
                [
                    'label' => 'General Average (%)',
                    'data' => $rows->pluck('general_average')->map(fn ($value) => $value === null ? null : (float) $value)->all(),
                    'borderColor' => 'rgb(14, 165, 233)',
                    'backgroundColor' => 'rgba(14, 165, 233, 0.15)',
                    'fill' => true,
                    'tension' => 0.3,
                    'pointRadius' => 5,
                    'pointBackgroundColor' => 'rgb(14, 165, 233)',
                ],
            ],
            'labels' => $rows->map(fn (PerformanceSummary $summary) => $summary->gradingPeriod->name)->all(),
        ];
    }
}
