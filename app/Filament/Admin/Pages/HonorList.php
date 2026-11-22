<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\AdminNavigationGroup;
use App\Models\PerformanceLevel;
use App\Models\PerformanceSummary;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class HonorList extends Page
{
    use HasPageShield;

    protected static ?string $navigationLabel = 'Honor List';

    protected static string|\UnitEnum|null $navigationGroup = AdminNavigationGroup::Reports;

    protected static ?int $navigationSort = 2;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    public function content(Schema $schema): Schema
    {
        $top = PerformanceLevel::query()->orderByDesc('min_grade')->first();
        $rows = $top === null
            ? collect()
            : PerformanceSummary::query()
                ->where('performance_level_id', $top->id)
                ->with('student.user', 'gradingPeriod', 'performanceLevel')
                ->get();

        return $schema->components([
            Html::make(fn () => view('filament.reports.simple-table', [
                'heading' => 'Honor list',
                'columns' => ['Student', 'Period', 'Average', 'Level'],
                'rows' => $rows->map(fn (PerformanceSummary $summary) => [
                    $summary->student->user->name,
                    $summary->gradingPeriod->name,
                    $summary->general_average,
                    $summary->performanceLevel?->name,
                ]),
            ])),
        ]);
    }
}
