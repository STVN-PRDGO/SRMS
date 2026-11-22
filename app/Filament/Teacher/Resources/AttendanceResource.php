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

    protected static ?string $model = Attendance::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('class_subject_id')
                ->relationship('classSubject', 'id', fn ($query) => $query->visibleTo(auth()->user()))
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->section->name.' · '.$record->subject->name)
                ->required()
                ->searchable()
                ->preload(),
            Select::make('student_id')->relationship('student.user', 'name')->required()->searchable()->preload(),
            DatePicker::make('date')->required(),
            Select::make('status')->options(AttendanceStatus::class)->required(),
            TextInput::make('remarks'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')->date(),
                TextColumn::make('student.user.name')->label('Student'),
                TextColumn::make('classSubject.subject.name')->label('Subject'),
                TextColumn::make('status')->badge(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageAttendances::route('/')];
    }
}
