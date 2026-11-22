<?php

namespace App\Filament\Teacher\Pages;

use App\Models\ClassSubject;
use App\Models\Grade;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ClassPerformance extends Page
{
    use HasPageShield;

    protected static ?string $navigationLabel = 'Performance';

    protected static ?string $title = 'Class Performance';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?int $navigationSort = 5;

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Html::make(fn () => view('filament.teacher.class-performance', [
                'rows' => Grade::query()
                    ->whereIn('class_subject_id', ClassSubject::query()->visibleTo(auth()->user())->pluck('id'))
                    ->with('student.user', 'classSubject.subject', 'gradingPeriod', 'student.performanceSummaries.performanceLevel')
                    ->latest('computed_at')
                    ->limit(100)
                    ->get(),
            ])),
        ]);
    }
}
