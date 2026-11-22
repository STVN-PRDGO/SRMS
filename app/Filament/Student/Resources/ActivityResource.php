<?php

namespace App\Filament\Student\Resources;

use App\Filament\Shared\Concerns\ScopesToStudent;
use App\Filament\Student\Resources\ActivityResource\Pages\ListActivities;
use App\Models\Activity;
use App\Support\StudentContext;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActivityResource extends Resource
{
    use ScopesToStudent;

    protected static ?string $model = Activity::class;

    protected static ?string $navigationLabel = 'Activities';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->searchable(),
            TextColumn::make('classSubject.subject.name')->label('Subject'),
            TextColumn::make('gradingPeriod.name')->label('Period'),
            TextColumn::make('due_date')->date(),
            TextColumn::make('max_score'),
            TextColumn::make('scores.score')
                ->label('My score')
                ->state(function (Activity $record): ?string {
                    $score = $record->scores->firstWhere('student_id', StudentContext::id());

                    return $score?->score === null ? $score?->status?->value : (string) $score->score;
                }),
        ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('scores');
    }

    public static function getPages(): array
    {
        return ['index' => ListActivities::route('/')];
    }
}
