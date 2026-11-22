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
        return $table
            ->columns([
                TextColumn::make('title')->label('Activity')->searchable()->sortable()->weight('bold'),
                TextColumn::make('type')->badge()->sortable(),
                TextColumn::make('classSubject.subject.name')->label('Subject')->sortable(),
                TextColumn::make('gradingPeriod.name')->label('Period')->sortable(),
                TextColumn::make('due_date')->date()->label('Due Date')->sortable(),
                TextColumn::make('max_score')->label('Max')->sortable(),
                TextColumn::make('scores.score')
                    ->label('My Score')
                    ->state(function (Activity $record): ?string {
                        $score = $record->scores->firstWhere('student_id', StudentContext::id());

                        return $score?->score === null ? ($score?->status?->value ? ucfirst($score->status->value) : 'Pending') : (string) $score->score;
                    })
                    ->badge()
                    ->color(function (Activity $record): string {
                        $score = $record->scores->firstWhere('student_id', StudentContext::id());
                        if ($score?->score !== null) {
                            $pct = ($score->score / max(1, $record->max_score)) * 100;

                            return $pct >= 75 ? 'success' : 'danger';
                        }

                        return 'gray';
                    }),
            ])
            ->defaultSort('due_date', 'desc')
            ->emptyStateHeading('No activities assigned')
            ->emptyStateDescription('Activities assigned by your subject teachers will appear here.')
            ->emptyStateIcon('heroicon-o-clipboard-document-list');
    }

    public static function getEloquentQuery(): Builder
    {
        $studentId = StudentContext::id();

        return parent::getEloquentQuery()->with([
            'scores' => fn ($query) => $studentId ? $query->where('student_id', $studentId) : $query,
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListActivities::route('/')];
    }
}
