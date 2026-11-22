<?php

namespace App\Filament\Student\Widgets;

use App\Models\Student;
use App\Services\AnalyticsService;
use App\Support\StudentContext;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StudentOverviewStatsWidget extends StatsOverviewWidget
{
    use HasWidgetShield;

    protected ?string $pollingInterval = null;

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $studentId = StudentContext::id();
        $student = $studentId ? Student::query()->find($studentId) : null;

        if (! $student) {
            return [
                Stat::make('Academic Profile', '—')
                    ->description('Select a student or sign in as a student to view records')
                    ->color('gray'),
            ];
        }

        $overview = app(AnalyticsService::class)->getStudentOverview($student);
        $avg = $overview['general_average'];
        $attRate = $overview['attendance_rate'];
        $compRate = $overview['activity_completion_rate'];

        $trendIcon = match ($overview['trend']) {
            'up' => 'heroicon-m-arrow-trending-up',
            'down' => 'heroicon-m-arrow-trending-down',
            default => 'heroicon-m-minus',
        };

        $trendColor = match ($overview['trend']) {
            'up' => 'success',
            'down' => 'danger',
            default => 'info',
        };

        $levelColor = match ($overview['performance_level']) {
            'Outstanding' => 'success',
            'Very Satisfactory' => 'info',
            'Satisfactory' => 'primary',
            'Fairly Satisfactory' => 'warning',
            default => 'danger',
        };

        return [
            Stat::make('General Average', $avg !== null ? "{$avg}%" : '—')
                ->description($overview['period_name'])
                ->descriptionIcon('heroicon-m-academic-cap')
                ->chart([80, 83, 85, 87, 86, $avg ? (int) $avg : 88])
                ->color('success'),

            Stat::make('Performance Level', $overview['performance_level'])
                ->description('Evaluation tier')
                ->descriptionIcon('heroicon-m-trophy')
                ->color($levelColor),

            Stat::make('Attendance Rate', $attRate !== null ? "{$attRate}%" : '—')
                ->description('Present & late rate')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color($attRate !== null && $attRate >= 85 ? 'success' : 'warning'),

            Stat::make('Activity Completion', $compRate !== null ? "{$compRate}%" : '—')
                ->description('Tasks submitted/graded')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color($compRate !== null && $compRate >= 80 ? 'success' : 'warning'),

            Stat::make('Performance Trend', ucfirst($overview['trend'] ?? 'Same'))
                ->description('Vs previous grading period')
                ->descriptionIcon($trendIcon)
                ->color($trendColor),
        ];
    }
}
