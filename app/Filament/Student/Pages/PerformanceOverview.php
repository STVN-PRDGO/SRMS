<?php

namespace App\Filament\Student\Pages;

use App\Models\PerformanceSummary;
use App\Support\StudentContext;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class PerformanceOverview extends Page
{
    use HasPageShield;

    protected static ?string $navigationLabel = 'Performance';

    protected static ?string $title = 'Performance';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?int $navigationSort = 4;

    public function content(Schema $schema): Schema
    {
        $studentId = StudentContext::id();

        return $schema->components([
            Html::make(fn () => view('filament.student.performance', [
                'rows' => $studentId === null
                    ? collect()
                    : PerformanceSummary::query()
                        ->where('student_id', $studentId)
                        ->with('gradingPeriod', 'performanceLevel')
                        ->get(),
            ])),
        ]);
    }
}
