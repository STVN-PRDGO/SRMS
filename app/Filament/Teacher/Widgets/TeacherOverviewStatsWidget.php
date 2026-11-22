<?php

namespace App\Filament\Teacher\Widgets;

use App\Services\AnalyticsService;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TeacherOverviewStatsWidget extends StatsOverviewWidget
{
    use HasWidgetShield;

    protected ?string $pollingInterval = null;

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $overview = app(AnalyticsService::class)->getTeacherOverview(auth()->user());
        $avgGrade = $overview['average_grade'];

        return [
            Stat::make('Assigned Classes', number_format($overview['classes_count']))
                ->description('Active sections & subjects')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('primary'),

            Stat::make('Total Students', number_format($overview['students_count']))
                ->description('Across your assigned classes')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('Class Average Grade', $avgGrade !== null ? "{$avgGrade}%" : '—')
                ->description('Overall academic average')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->chart([75, 78, 80, 82, 85, 84, $avgGrade ? (int) $avgGrade : 80])
                ->color('success'),

            Stat::make('Pending Grading', number_format($overview['pending_activities']))
                ->description('Activities awaiting score completion')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color($overview['pending_activities'] > 0 ? 'warning' : 'success'),

            Stat::make('At-Risk Students', number_format($overview['at_risk_count']))
                ->description('Needs academic intervention')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($overview['at_risk_count'] > 0 ? 'danger' : 'success'),
        ];
    }
}
