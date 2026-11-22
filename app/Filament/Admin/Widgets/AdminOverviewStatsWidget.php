<?php

namespace App\Filament\Admin\Widgets;

use App\Services\AnalyticsService;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminOverviewStatsWidget extends StatsOverviewWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $overview = app(AnalyticsService::class)->getAdminOverview();

        $avgGrade = $overview['school_average'];
        $attRate = $overview['attendance_rate'];
        $periodName = $overview['active_period']?->name ?? 'No Open Period';

        return [
            Stat::make('Active Students', number_format($overview['total_students']))
                ->description('Enrolled in school year')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success'),

            Stat::make('Faculty Teachers', number_format($overview['total_teachers']))
                ->description('Active teaching staff')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make('Class Sections', number_format($overview['total_sections']))
                ->description($overview['active_school_year'])
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('primary'),

            Stat::make('School-Wide Average', $avgGrade !== null ? "{$avgGrade}%" : '—')
                ->description("Period: {$periodName}")
                ->descriptionIcon('heroicon-m-chart-bar')
                ->chart([78, 81, 80, 84, 83, 86, $avgGrade ? (int) $avgGrade : 85])
                ->color('info'),

            Stat::make('Attendance Rate', $attRate !== null ? "{$attRate}%" : '—')
                ->description('Present & late rate')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color($attRate !== null && $attRate >= 85 ? 'success' : 'warning'),
        ];
    }
}
