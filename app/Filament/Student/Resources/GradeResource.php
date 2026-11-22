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
                TextColumn::make('gradingPeriod.name')->label('Quarter / Period')->sortable(),
                TextColumn::make('classSubject.subject.name')->label('Subject')->sortable()->weight('bold'),
                TextColumn::make('raw_grade')->label('Grade')->numeric(decimalPlaces: 2)->sortable(),
                TextColumn::make('letter')->label('Letter')->badge()->sortable(),
                TextColumn::make('remark')->label('Remarks')->sortable(),
            ])
            ->headerActions([
                Action::make('reportCard')
                    ->label('Download Report Card')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->color('primary')
                    ->visible(fn (): bool => (bool) auth()->user()?->can('download_report_card'))
                    ->schema([
                        Select::make('grading_period_id')
                            ->label('Grading Period')
                            ->options(fn () => GradingPeriod::query()->pluck('name', 'id'))
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $student = Student::query()->find(StudentContext::id());
                        $period = GradingPeriod::query()->findOrFail((int) $data['grading_period_id']);

                        if ($student === null) {
                            abort(403);
                        }

                        $pdf = app(ReportCardService::class)->generate($student, $period);

                        return Response::streamDownload(
                            fn () => print ($pdf),
                            "report-card-{$student->student_no}-{$period->name}.html",
                            ['Content-Type' => 'text/html']
                        );
                    }),
            ])
            ->emptyStateHeading('No grades recorded yet')
            ->emptyStateDescription('Grades will be published once teacher assessments are finalized.')
            ->emptyStateIcon('heroicon-o-academic-cap');
    }

    public static function getPages(): array
    {
        return ['index' => ManageGrades::route('/')];
    }
}
