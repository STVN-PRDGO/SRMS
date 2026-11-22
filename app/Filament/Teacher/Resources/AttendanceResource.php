<?php

namespace App\Filament\Teacher\Resources;

use App\Enums\AttendanceStatus;
use App\Filament\Shared\Concerns\ScopesByPermission;
use App\Filament\Teacher\Resources\AttendanceResource\Pages\ManageAttendances;
use App\Models\Attendance;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AttendanceResource extends Resource
{
    use ScopesByPermission;

    protected static bool $isGloballySearchable = false;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $model = Attendance::class;

    protected static ?string $navigationLabel = 'Attendance';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('class_subject_id')
                ->relationship('classSubject', 'id', fn ($query) => $query->visibleTo(auth()->user())->with(['section', 'subject']))
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->section->name.' · '.$record->subject->name)
                ->required()
                ->searchable()
                ->preload(),
            Select::make('student_id')
                ->relationship('student.user', 'name')
                ->required()
                ->searchable(),
            DatePicker::make('date')
                ->native(false)
                ->default(now())
                ->required(),
            Select::make('status')
                ->options(AttendanceStatus::class)
                ->required()
                ->default(AttendanceStatus::Present),
            TextInput::make('remarks')
                ->placeholder('Optional reason or notes')
                ->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')->date()->sortable(),
                TextColumn::make('student.user.name')->label('Student')->searchable()->sortable(),
                TextColumn::make('classSubject.subject.name')->label('Subject')->sortable(),
                TextColumn::make('classSubject.section.name')->label('Section')->sortable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('remarks')->label('Remarks')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date', 'desc')
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->emptyStateHeading('No attendance records')
            ->emptyStateDescription('Record attendance logs for your classes.')
            ->emptyStateIcon('heroicon-o-calendar-days');
    }

    public static function getPages(): array
    {
        return ['index' => ManageAttendances::route('/')];
    }
}
