<?php

namespace App\Filament\Teacher\Pages;

use App\Actions\Scores\SaveScores;
use App\Enums\ScoreStatus;
use App\Filament\Teacher\Concerns\ResolvesTaughtStudents;
use App\Models\Activity;
use App\Models\Grade;
use App\Models\GradingPeriod;
use App\Models\Student;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;

class StudentRecord extends Page
{
    use ResolvesTaughtStudents;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'my-students/{student}';

    #[Locked]
    public int $student;

    #[Locked]
    public ?Student $record = null;

    public string $term = '';

    public string $appliedTerm = '';

    /**
     * @var array<int, array{score: string, status: string}>
     */
    public array $rows = [];

    public static function canAccess(): bool
    {
        return MyStudents::canAccess();
    }

    public function mount(int $student): void
    {
        $record = Student::query()->findOrFail($student);
        abort_unless($this->teachesStudent(auth()->user(), $record), 403);

        $this->student = $student;
        $this->record = $record->load('user', 'section.gradeLevel', 'section.schoolYear');
        $this->subheading = $record->student_no;
        $this->loadRows();
    }

    public function getTitle(): string
    {
        return $this->record?->user->name ?? 'Student record';
    }

    public function applyTerm(): void
    {
        $allowed = $this->terms()->pluck('id')->map(fn ($id): string => (string) $id);

        $this->appliedTerm = $allowed->contains($this->term) ? $this->term : '';
        $this->term = $this->appliedTerm;
        $this->loadRows();
    }

    public function save(): void
    {
        abort_unless($this->record !== null && $this->teachesStudent(auth()->user(), $this->record), 403);

        $saved = 0;
        $skipped = 0;

        DB::transaction(function () use (&$saved, &$skipped): void {
            foreach ($this->editableActivities() as $activity) {
                $row = $this->rows[$activity->id] ?? null;

                if ($row === null || (($row['status'] ?? '') === '' && ($row['score'] ?? '') === '')) {
                    continue;
                }

                if (! $activity->gradingPeriod->acceptsEntries()) {
                    $skipped++;

                    continue;
                }

                app(SaveScores::class)($activity, [[
                    'student_id' => $this->record->id,
                    'score' => $row['score'] ?? null,
                    'status' => $row['status'] ?? null,
                ]], auth()->user());
                $saved++;
            }
        });

        $this->loadRows();

        $notification = Notification::make()->title(
            $saved > 0 ? 'Scores saved. The grade was recomputed.' : 'No open scores were changed.'
        );

        if ($skipped > 0) {
            $notification->body('Scores in a closed or draft term were left unchanged.');
        }

        $notification->success()->send();
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Html::make(fn () => view('filament.teacher.student-record', [
                'student' => $this->record,
                'terms' => $this->terms(),
                'activities' => $this->editableActivities(),
                'grades' => $this->grades(),
                'statuses' => ScoreStatus::cases(),
                'errorMessage' => $this->getErrorBag()->first() ?? '',
            ])),
        ]);
    }

    /**
     * @return Collection<int, GradingPeriod>
     */
    public function terms(): Collection
    {
        return GradingPeriod::query()
            ->where('school_year_id', $this->record->section->school_year_id)
            ->orderBy('starts_on')
            ->get();
    }

    /**
     * @return Collection<int, Activity>
     */
    public function editableActivities(): Collection
    {
        return Activity::query()
            ->whereHas('classSubject', fn ($query) => $query
                ->visibleTo(auth()->user())
                ->where('section_id', $this->record->section_id))
            ->when($this->appliedTerm !== '', fn ($query) => $query->where('grading_period_id', (int) $this->appliedTerm))
            ->with([
                'gradingPeriod',
                'gradingComponent',
                'classSubject.subject',
                'scores' => fn ($query) => $query->where('student_id', $this->record->id),
            ])
            ->orderBy('due_date')
            ->orderBy('title')
            ->get();
    }

    /**
     * @return Collection<int, Grade>
     */
    public function grades(): Collection
    {
        return Grade::query()
            ->where('student_id', $this->record->id)
            ->whereHas('classSubject', fn ($query) => $query
                ->visibleTo(auth()->user())
                ->where('section_id', $this->record->section_id))
            ->when($this->appliedTerm !== '', fn ($query) => $query->where('grading_period_id', (int) $this->appliedTerm))
            ->with('classSubject.subject', 'gradingPeriod')
            ->get();
    }

    public function loadRows(): void
    {
        $this->rows = [];

        foreach ($this->editableActivities() as $activity) {
            $score = $activity->scores->first();
            $this->rows[$activity->id] = [
                'score' => $score?->score === null ? '' : (string) $score->score,
                'status' => $score?->status?->value ?? '',
            ];
        }
    }
}
