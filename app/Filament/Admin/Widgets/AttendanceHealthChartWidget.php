<?php

namespace App\Filament\Admin\Widgets;

use App\Services\AnalyticsService;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;

class AttendanceHealthChartWidget extends ChartWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    protected ?string $heading = 'School Attendance Health';

    protected ?string $description = 'Distribution of attendance records in the current period';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $distribution = app(AnalyticsService::class)->getAttendanceDistribution();

        return [
            'datasets' => [
                [
                    'label' => 'Attendance Records',
                    'data' => $distribution['data'],
                    'backgroundColor' => [
                        'rgba(16, 185, 129, 0.8)', // Present (Emerald)
                        'rgba(245, 158, 11, 0.8)', // Late (Amber)
                        'rgba(244, 63, 94, 0.8)',  // Absent (Rose)
                        'rgba(148, 163, 184, 0.8)', // Excused (Slate)
                    ],
                ],
            ],
            'labels' => $distribution['labels'],
        ];
    }
}
