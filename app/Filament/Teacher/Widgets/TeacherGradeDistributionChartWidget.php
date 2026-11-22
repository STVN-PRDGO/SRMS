<?php

namespace App\Filament\Teacher\Widgets;

use App\Services\AnalyticsService;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;

class TeacherGradeDistributionChartWidget extends ChartWidget
{
    use HasWidgetShield;

    protected ?string $pollingInterval = null;

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected ?string $heading = 'Grade Distribution';

    protected ?string $description = 'Student grade scale breakdown in your classes';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $distribution = app(AnalyticsService::class)->getTeacherGradeDistribution(auth()->user());

        return [
            'datasets' => [
                [
                    'label' => 'Students',
                    'data' => $distribution['data'],
                    'backgroundColor' => [
                        'rgba(16, 185, 129, 0.8)', // A (Emerald)
                        'rgba(14, 165, 233, 0.8)', // B (Sky)
                        'rgba(245, 158, 11, 0.8)', // C (Amber)
                        'rgba(249, 115, 22, 0.8)', // D (Orange)
                        'rgba(244, 63, 94, 0.8)',  // F (Rose)
                    ],
                ],
            ],
            'labels' => $distribution['labels'],
        ];
    }
}
