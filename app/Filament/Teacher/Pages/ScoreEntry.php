<?php

namespace App\Filament\Teacher\Pages;

use App\Actions\Scores\SaveScores;
use App\Enums\ScoreStatus;
use App\Models\Activity;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ScoreEntry extends Page
{
    use HasPageShield;

    protected static ?string $navigationLabel = 'Scores';

    protected static ?string $title = 'Scores';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;

    protected static ?int $navigationSort = 4;

    public ?array $data = [];

    public function mount(): void
    {
        $this->data = ['activity_id' => null, 'rows' => []];
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('activity_id')
                ->label('Activity')
                ->options(fn (): array => Activity::query()
                    ->whereHas('classSubject', fn ($query) => $query->visibleTo(auth()->user()))
                    ->with(['classSubject.section', 'classSubject.subject', 'gradingPeriod'])
                    ->get()
                    ->mapWithKeys(fn (Activity $activity): array => [
                        $activity->id => $activity->title.' · '.$activity->classSubject->section->name.' · '.$activity->classSubject->subject->name.' · '.$activity->gradingPeriod->name,
                    ])
                    ->all())
                ->live()
                ->afterStateUpdated(fn (?string $state) => $this->loadRows($state))
                ->helperText(function (?string $state): ?string {
                    if (! $state) {
                        return 'The grade and remark are computed from these scores.';
                    }

                    $activity = Activity::query()->with('gradingComponent')->find($state);

                    return $activity
                        ? "Component: {$activity->gradingComponent?->name} | Max score: {$activity->max_score} | Due: ".($activity->due_date?->format('M d, Y') ?? 'No due date')
                        : null;
                })
                ->required(),
            Repeater::make('rows')
                ->schema([
                    Hidden::make('student_id'),
                    TextInput::make('student_name')
                        ->label('Student')
                        ->disabled()
                        ->dehydrated(),
                    TextInput::make('score')
                        ->label('Score')
                        ->numeric()
                        ->placeholder('Earned score'),
                    Select::make('status')
                        ->label('Status')
                        ->options(ScoreStatus::class)
                        ->default(ScoreStatus::Graded),
                ])
                ->addable(false)
                ->deletable(false)
                ->columns(3),
        ])->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save scores')
                ->action(function (): void {
                    $activity = Activity::query()->findOrFail($this->data['activity_id'] ?? null);
                    app(SaveScores::class)($activity, $this->data['rows'] ?? [], auth()->user());
                    Notification::make()->title('Scores saved. Grades were recomputed.')->success()->send();
                }),
        ];
    }

    public function loadRows(?string $activityId): void
    {
        $activity = Activity::query()
            ->with('classSubject.section.students.user', 'scores')
            ->find($activityId);
        $this->data['rows'] = [];

        if ($activity === null || ! $activity->classSubject->isManageableBy(auth()->user())) {
            return;
        }

        foreach ($activity->classSubject->section->students as $student) {
            $score = $activity->scores->firstWhere('student_id', $student->id);
            $this->data['rows'][] = [
                'student_id' => $student->id,
                'student_name' => $student->user->name.' ('.$student->student_no.')',
                'score' => $score?->score,
                'status' => $score?->status?->value ?? ScoreStatus::Graded->value,
            ];
        }
    }
}
