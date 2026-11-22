<?php

namespace App\Filament\Teacher\Widgets;

use App\Services\AnalyticsService;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;

class TeacherClassPerformanceChartWidget extends ChartWidget
{
    use HasWidgetShield;

    protected ?string $pollingInterval = null;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = [
        'default' => 1,
        'lg' => 2,
    ];

    protected ?string $heading = 'Class Subject Performance Comparison';

    protected ?string $description = 'Mean grade per assigned subject and section';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $comparison = app(AnalyticsService::class)->getTeacherClassComparison(auth()->user());

        return [
            'datasets' => [
                [
                    'label' => 'Class Average (%)',
                    'data' => $comparison['data'],
                    'backgroundColor' => 'rgba(59, 130, 246, 0.75)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 1,
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $comparison['labels'],
        ];
    }
}
