<?php

namespace App\Filament\Admin\Pages;

use App\Models\Grade;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SectionAverages extends Page
{
    use HasPageShield;

    protected static ?string $navigationLabel = 'Section Averages';

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    public function content(Schema $schema): Schema
    {
        $rows = Grade::query()
            ->with('classSubject.section', 'gradingPeriod')
            ->whereNotNull('raw_grade')
            ->get()
            ->groupBy(fn (Grade $grade) => $grade->classSubject->section->name.' · '.$grade->gradingPeriod->name)
            ->map(fn ($grades, $label) => ['label' => $label, 'average' => round((float) $grades->avg('raw_grade'), 2)]);

        return $schema->components([
            Html::make(fn () => view('filament.reports.simple-table', [
                'heading' => 'Section averages',
                'columns' => ['Section and period', 'Average'],
                'rows' => $rows->map(fn ($row) => [$row['label'], number_format($row['average'], 2)])->values(),
            ])),
        ]);
    }
}
