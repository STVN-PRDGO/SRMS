<?php

namespace App\Filament\Admin\Resources;

use App\Actions\Students\ImportStudents;
use App\Enums\StudentStatus;
use App\Filament\Admin\AdminNavigationGroup;
use App\Filament\Admin\Resources\StudentResource\Pages\ManageStudents;
use App\Models\SchoolYear;
use App\Models\Student;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
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

    protected static string|\UnitEnum|null $navigationGroup = AdminNavigationGroup::People;

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Full Name')
                ->prefixIcon('heroicon-m-user')
                ->required()
                ->maxLength(255)
                ->visibleOn('create'),
            TextInput::make('email')
                ->label('Email Address')
                ->prefixIcon('heroicon-m-envelope')
                ->email()
                ->required()
                ->maxLength(255)
                ->visibleOn('create'),
            TextInput::make('password')
                ->password()
                ->prefixIcon('heroicon-m-key')
                ->required()
                ->visibleOn('create'),
            TextInput::make('student_no')
                ->label('Student Number')
                ->prefixIcon('heroicon-m-identification')
                ->required()
                ->unique(ignoreRecord: true),
            Select::make('section_id')
                ->label('Section')
                ->relationship('section', 'name')
                ->required()
                ->searchable()
                ->preload(),
            DatePicker::make('birthdate')
                ->label('Date of Birth')
                ->native(false)
                ->maxDate(now())
                ->displayFormat('M d, Y'),
            TextInput::make('guardian_name')
                ->label('Guardian Name')
                ->prefixIcon('heroicon-m-user')
                ->maxLength(255),
            TextInput::make('guardian_contact')
                ->label('Guardian Contact')
                ->tel()
                ->prefixIcon('heroicon-m-phone')
                ->placeholder('+63 900 000 0000')
                ->maxLength(50),
            Select::make('status')
                ->options(StudentStatus::class)
                ->required()
                ->default(StudentStatus::Active),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Name')->searchable()->sortable(),
                TextColumn::make('student_no')->label('Student No')->searchable()->sortable(),
                TextColumn::make('section.name')->label('Section')->searchable()->sortable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('guardian_name')->label('Guardian')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('guardian_contact')->label('Contact')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Action::make('import')
                    ->label('Import CSV')
                    ->icon('heroicon-m-arrow-up-tray')
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
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->emptyStateHeading('No students found')
            ->emptyStateDescription('Create a student record or import from a CSV roster.')
            ->emptyStateIcon('heroicon-o-academic-cap');
    }

    public static function getPages(): array
    {
        return ['index' => ManageStudents::route('/')];
    }
}
