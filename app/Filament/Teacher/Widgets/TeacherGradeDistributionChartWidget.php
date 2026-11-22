<?php

namespace App\Filament\Teacher\Widgets;

use App\Services\AnalyticsService;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Support\Components\Attributes\Unexposed;
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

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'cutout' => '68%',
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'color' => '#d8dee9',
                        'usePointStyle' => true,
                        'pointStyle' => 'circle',
                        'boxWidth' => 8,
                        'padding' => 14,
                    ],
                ],
                'tooltip' => [
                    'backgroundColor' => '#111827',
                    'titleColor' => '#f3f4f6',
                    'bodyColor' => '#d1d5db',
                    'borderColor' => '#374151',
                    'borderWidth' => 1,
                ],
            ],
        ];
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

    #[Unexposed]
    public function getChartAssistiveContent(): string
    {
        $chartData = $this->getCachedData();
        $values = $chartData['datasets'][0]['data'] ?? [];
        $summary = collect($chartData['labels'] ?? [])
            ->map(fn (string $label, int $index): string => $label.': '.number_format((int) ($values[$index] ?? 0)))
            ->implode('; ');

        return 'Grade distribution across your classes: '.$summary;
    }
}
