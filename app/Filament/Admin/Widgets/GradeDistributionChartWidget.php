<?php

namespace App\Filament\Admin\Widgets;

use App\Services\AnalyticsService;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;

class GradeDistributionChartWidget extends ChartWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = [
        'default' => 1,
        'xl' => 2,
    ];

    protected ?string $heading = 'Student Performance Level Distribution';

    protected ?string $description = 'Distribution of students across evaluated academic performance tiers';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $distribution = app(AnalyticsService::class)->getPerformanceLevelDistribution();

        return [
            'datasets' => [
                [
                    'label' => 'Students',
                    'data' => $distribution['data'],
                    'backgroundColor' => [
                        'rgba(16, 185, 129, 0.75)', // Emerald (Outstanding)
                        'rgba(14, 165, 233, 0.75)', // Sky (Very Satisfactory)
                        'rgba(245, 158, 11, 0.75)', // Amber (Satisfactory)
                        'rgba(249, 115, 22, 0.75)', // Orange (Fairly Satisfactory)
                        'rgba(244, 63, 94, 0.75)',  // Rose (Needs Improvement)
                    ],
                    'borderColor' => [
                        'rgb(16, 185, 129)',
                        'rgb(14, 165, 233)',
                        'rgb(245, 158, 11)',
                        'rgb(249, 115, 22)',
                        'rgb(244, 63, 94)',
                    ],
                    'borderWidth' => 1,
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $distribution['labels'],
        ];
    }
}
