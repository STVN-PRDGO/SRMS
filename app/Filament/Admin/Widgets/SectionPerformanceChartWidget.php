<?php

namespace App\Filament\Admin\Widgets;

use App\Services\AnalyticsService;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;

class SectionPerformanceChartWidget extends ChartWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected ?string $heading = 'Section Averages';

    protected ?string $description = 'Mean student raw grade by class section';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $data = app(AnalyticsService::class)->getSectionAveragesChart();

        return [
            'datasets' => [
                [
                    'label' => 'Section Average',
                    'data' => $data['data'],
                    'backgroundColor' => 'rgba(99, 102, 241, 0.75)',
                    'borderColor' => 'rgb(99, 102, 241)',
                    'borderWidth' => 1,
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $data['labels'],
        ];
    }
}
