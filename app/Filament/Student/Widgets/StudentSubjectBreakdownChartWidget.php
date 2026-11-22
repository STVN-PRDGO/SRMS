<?php

namespace App\Filament\Student\Widgets;

use App\Models\Student;
use App\Services\AnalyticsService;
use App\Support\StudentContext;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;

class StudentSubjectBreakdownChartWidget extends ChartWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    protected ?string $heading = 'Subject Grades';

    protected ?string $description = 'Your computed grades by subject for the active quarter';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $studentId = StudentContext::id();
        $student = $studentId ? Student::query()->find($studentId) : null;

        if (! $student) {
            return ['labels' => [], 'data' => []];
        }

        $breakdown = app(AnalyticsService::class)->getStudentSubjectBreakdown($student);

        return [
            'datasets' => [
                [
                    'label' => 'Subject Grade (%)',
                    'data' => $breakdown['data'],
                    'backgroundColor' => 'rgba(16, 185, 129, 0.75)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'borderWidth' => 1,
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $breakdown['labels'],
        ];
    }
}
