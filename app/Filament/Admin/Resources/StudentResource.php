<?php

namespace App\Filament\Admin\Resources;

use App\Actions\Students\ImportStudents;
use App\Enums\StudentStatus;
use App\Filament\Admin\Resources\StudentResource\Pages\ManageStudents;
use App\Models\SchoolYear;
use App\Models\Student;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|\UnitEnum|null $navigationGroup = 'Users';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->visibleOn('create'),
            TextInput::make('email')->email()->required()->visibleOn('create'),
            TextInput::make('password')->password()->required()->visibleOn('create'),
            TextInput::make('student_no')->required()->unique(ignoreRecord: true),
            Select::make('section_id')->relationship('section', 'name')->required()->searchable()->preload(),
            TextInput::make('birthdate'),
            TextInput::make('guardian_name'),
            TextInput::make('guardian_contact'),
            Select::make('status')->options(StudentStatus::class)->required()->default(StudentStatus::Active),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Name')->searchable(),
                TextColumn::make('student_no')->searchable(),
                TextColumn::make('section.name')->label('Section'),
                TextColumn::make('status')->badge(),
            ])
            ->headerActions([
                Action::make('import')
                    ->label('Import CSV')
                    ->schema([
                        Select::make('school_year_id')->options(fn () => SchoolYear::query()->pluck('name', 'id'))->required(),
                        FileUpload::make('file')->required()->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel']),
                    ])
                    ->action(function (array $data): void {
                        $path = Storage::disk('local')->path($data['file']);
                        $result = app(ImportStudents::class)(file_get_contents($path) ?: '', (int) $data['school_year_id']);
                        Notification::make()
                            ->title("Imported {$result['created']} students")
                            ->body("Skipped {$result['skipped']} existing student numbers.")
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageStudents::route('/')];
    }
}
