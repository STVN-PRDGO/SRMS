<?php

namespace App\Filament\Teacher\Pages;

use App\Filament\Shared\WelcomeBanner;
use App\Filament\Teacher\Widgets\ClassAverageWidget;
use App\Filament\Teacher\Widgets\PendingGradingWidget;
use App\Filament\Teacher\Widgets\TeacherClassPerformanceChartWidget;
use App\Filament\Teacher\Widgets\TeacherGradeDistributionChartWidget;
use App\Filament\Teacher\Widgets\TeacherOverviewStatsWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\Widget;

class Dashboard extends BaseDashboard
{
    protected static bool $isDiscovered = false;

    /**
     * @return array<class-string<Widget>>
     */
    public function getWidgets(): array
    {
        return [
            WelcomeBanner::class,
            ClassAverageWidget::class,
            PendingGradingWidget::class,
            TeacherOverviewStatsWidget::class,
            TeacherClassPerformanceChartWidget::class,
            TeacherGradeDistributionChartWidget::class,
        ];
    }

    public function getPageClasses(): array
    {
        return ['srms-teacher-dashboard'];
    }

    public function getSubheading(): string
    {
        return 'Welcome back, '.(auth()->user()?->name ?? 'Teacher').'!';
    }

    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'lg' => 3,
        ];
    }
}
