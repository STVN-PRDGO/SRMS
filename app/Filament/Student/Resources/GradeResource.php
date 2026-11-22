<?php

namespace App\Filament\Student\Resources;

use App\Filament\Shared\Concerns\ScopesToStudent;
use App\Filament\Student\Resources\GradeResource\Pages\ManageGrades;
use App\Models\Grade;
use App\Models\GradingPeriod;
use App\Models\Student;
use App\Services\ReportCardService;
use App\Support\StudentContext;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Response;

class GradeResource extends Resource
{
    use ScopesToStudent;

    protected static ?string $model = Grade::class;

    protected static ?string $navigationLabel = 'My Grades';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('raw_grade')->numeric(),
            TextInput::make('letter'),
            TextInput::make('remark'),
            Select::make('student_id')->relationship('student', 'student_no')->required(),
            Select::make('class_subject_id')->relationship('classSubject', 'id')->required(),
            Select::make('grading_period_id')->relationship('gradingPeriod', 'name')->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('gradingPeriod.name')->label('Period'),
                TextColumn::make('classSubject.subject.name')->label('Subject'),
                TextColumn::make('raw_grade')->label('Grade'),
                TextColumn::make('letter'),
                TextColumn::make('remark'),
            ])
            ->headerActions([
                Action::make('reportCard')
                    ->label('Download report card')
                    ->visible(fn (): bool => (bool) auth()->user()?->can('download_report_card'))
                    ->schema([
                        Select::make('grading_period_id')
                            ->options(fn () => GradingPeriod::query()->where('status', 'closed')->pluck('name', 'id'))
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $period = GradingPeriod::query()->findOrFail($data['grading_period_id']);
                        abort_unless($period->isClosed(), 403);
                        $student = Student::query()->findOrFail(StudentContext::id());
                        $html = app(ReportCardService::class)->html($student, $period);

                        return Response::make($html, 200, [
                            'Content-Type' => 'text/html; charset=UTF-8',
                            'Content-Disposition' => 'attachment; filename="report-card.html"',
                        ]);
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageGrades::route('/')];
    }
}
