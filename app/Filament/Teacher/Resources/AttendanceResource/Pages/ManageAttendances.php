<?php

namespace App\Filament\Teacher\Resources\AttendanceResource\Pages;

use App\Enums\AttendanceStatus;
use App\Filament\Teacher\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Models\ClassSubject;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;

class ManageAttendances extends Page
{
    protected static string $resource = AttendanceResource::class;

    protected static ?string $title = 'Attendance';

    public ?array $data = [];

    public function mount(): void
    {
        $this->data = [
            'class_subject_id' => null,
            'date' => now()->toDateString(),
            'rows' => [],
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('class_subject_id')
                ->label('Class')
                ->options(fn (): array => $this->classOptions())
                ->live()
                ->afterStateUpdated(fn () => $this->loadRows())
                ->required(),
            DatePicker::make('date')
                ->label('Date')
                ->native(false)
                ->default(now()->toDateString())
                ->live()
                ->afterStateUpdated(fn () => $this->loadRows())
                ->required(),
            Repeater::make('rows')
                ->label('Students')
                ->schema([
                    Hidden::make('student_id'),
                    TextInput::make('student_name')
                        ->label('Student')
                        ->disabled()
                        ->dehydrated(),
                    Select::make('status')
                        ->label('Status')
                        ->options(AttendanceStatus::class)
                        ->required(),
                ])
                ->helperText('Choose the class and date, mark anyone who is not present, then save once.')
                ->addable(false)
                ->deletable(false)
                ->columns(2),
        ])->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save attendance')
                ->icon('heroicon-m-check')
                ->visible(fn (): bool => filled($this->data['class_subject_id'] ?? null) && filled($this->data['rows'] ?? []))
                ->action(fn () => $this->save()),
        ];
    }

    public function loadRows(): void
    {
        $class = $this->selectedClass();
        $date = $this->selectedDate();
        $this->data['rows'] = [];

        if ($class === null || $date === null) {
            return;
        }

        $existing = Attendance::query()
            ->where('class_subject_id', $class->id)
            ->whereDate('date', $date)
            ->get()
            ->keyBy('student_id');

        foreach ($class->section->students->sortBy(fn ($student) => $student->user->name) as $student) {
            $record = $existing->get($student->id);
            $this->data['rows'][] = [
                'student_id' => $student->id,
                'student_name' => $student->user->name.' ('.$student->student_no.')',
                'status' => $record?->status?->value ?? AttendanceStatus::Present->value,
            ];
        }
    }

    public function save(): void
    {
        $class = $this->selectedClass();
        $date = $this->selectedDate();

        if ($class === null || $date === null || blank($this->data['rows'] ?? [])) {
            Notification::make()->title('Choose a class and date first')->warning()->send();

            return;
        }

        foreach ($this->data['rows'] as $row) {
            $attendance = Attendance::query()->firstOrNew([
                'class_subject_id' => $class->id,
                'student_id' => $row['student_id'],
                'date' => $date,
            ]);
            $attendance->status = $row['status'] ?? AttendanceStatus::Present->value;
            $attendance->save();
        }

        Notification::make()->title('Attendance saved')->success()->send();
    }

    /**
     * @return array<int|string, string>
     */
    private function classOptions(): array
    {
        return ClassSubject::query()
            ->visibleTo(auth()->user())
            ->with(['section', 'subject'])
            ->get()
            ->mapWithKeys(fn (ClassSubject $class): array => [
                $class->id => $class->section->name.' · '.$class->subject->name,
            ])
            ->all();
    }

    private function selectedClass(): ?ClassSubject
    {
        $id = $this->data['class_subject_id'] ?? null;

        if (blank($id)) {
            return null;
        }

        $class = ClassSubject::query()
            ->with(['section.students.user', 'subject'])
            ->find($id);

        if ($class === null || ! $class->isManageableBy(auth()->user())) {
            return null;
        }

        return $class;
    }

    private function selectedDate(): ?string
    {
        $date = $this->data['date'] ?? null;

        if ($date instanceof Carbon) {
            return $date->toDateString();
        }

        if (! is_string($date) || blank($date)) {
            return null;
        }

        return Carbon::parse($date)->toDateString();
    }
}
