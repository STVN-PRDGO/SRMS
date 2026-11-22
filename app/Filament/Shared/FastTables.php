<?php

namespace App\Filament\Shared;

use App\Models\Activity;
use App\Models\Attendance;
use App\Models\ClassSubject;
use App\Models\Grade;
use App\Models\GradingComponent;
use App\Models\GradingPeriod;
use App\Models\Score;
use App\Models\Section;
use App\Models\Student;
use App\Models\Teacher;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FastTables
{
    public static function register(): void
    {
        Table::configureUsing(function (Table $table): void {
            $table
                ->lazyLoaded()
                ->striped()
                ->modifyQueryUsing(fn (Builder $query): Builder => self::withRelations($query));
        });
    }

    public static function withRelations(Builder $query): Builder
    {
        if ($query->getModel() instanceof ClassSubject) {
            return $query->with([
                'subject',
                'teacher.user',
                'section' => fn ($section) => $section->with('gradeLevel')->withCount('students'),
            ]);
        }

        $with = match ($query->getModel()::class) {
            Activity::class => ['classSubject.subject', 'gradingPeriod', 'gradingComponent'],
            Attendance::class => ['student.user', 'classSubject.subject'],
            Score::class => ['activity', 'student.user'],
            Grade::class => ['gradingPeriod', 'classSubject.subject'],
            Student::class => ['user', 'section'],
            Teacher::class => ['user'],
            Section::class => ['schoolYear', 'gradeLevel', 'adviser'],
            GradingPeriod::class => ['schoolYear'],
            GradingComponent::class => ['subject'],
            default => [],
        };

        return $with === [] ? $query : $query->with($with);
    }
}
