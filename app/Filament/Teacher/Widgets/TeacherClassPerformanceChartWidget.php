<?php

namespace App\Filament\Teacher\Widgets;

use App\Services\AnalyticsService;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Support\Components\Attributes\Unexposed;
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

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'labels' => [
                        'color' => '#d8dee9',
                        'usePointStyle' => true,
                        'boxWidth' => 8,
                        'padding' => 18,
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
            'scales' => [
                'x' => [
                    'ticks' => ['color' => '#aab2c0'],
                    'grid' => ['display' => false],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'max' => 100,
                    'ticks' => ['color' => '#aab2c0'],
                    'grid' => ['color' => 'rgba(148, 163, 184, 0.12)'],
                ],
            ],
        ];
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

    #[Unexposed]
    public function getChartAssistiveContent(): string
    {
        $chartData = $this->getCachedData();
        $values = $chartData['datasets'][0]['data'] ?? [];
        $summary = collect($chartData['labels'] ?? [])
            ->map(fn (string $label, int $index): string => $label.': '.number_format((float) ($values[$index] ?? 0), 2).'%')
            ->implode('; ');

        return 'Class averages by assigned subject and section: '.$summary;
    }
}
