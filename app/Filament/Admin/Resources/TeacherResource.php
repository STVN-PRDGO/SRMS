<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\AdminNavigationGroup;
use App\Filament\Admin\Resources\TeacherResource\Pages\ManageTeachers;
use App\Models\Teacher;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TeacherResource extends Resource
{
    protected static ?string $model = Teacher::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static string|\UnitEnum|null $navigationGroup = AdminNavigationGroup::People;

    protected static ?int $navigationSort = 2;

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
            TextInput::make('employee_no')
                ->label('Employee Number')
                ->prefixIcon('heroicon-m-identification')
                ->required()
                ->unique(ignoreRecord: true),
            TextInput::make('department')
                ->label('Department')
                ->prefixIcon('heroicon-m-building-office')
                ->placeholder('e.g. Science, Mathematics')
                ->maxLength(100),
            TextInput::make('contact_no')
                ->label('Contact Number')
                ->tel()
                ->prefixIcon('heroicon-m-phone')
                ->placeholder('+63 900 000 0000')
                ->maxLength(50),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Name')->searchable()->sortable(),
                TextColumn::make('employee_no')->label('Employee No')->searchable()->sortable(),
                TextColumn::make('department')->label('Department')->searchable()->sortable(),
                TextColumn::make('contact_no')->label('Contact')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('class_subjects_count')->counts('classSubjects')->label('Assigned Classes')->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->emptyStateHeading('No teachers found')
            ->emptyStateDescription('Create teacher accounts to assign subjects and sections.')
            ->emptyStateIcon('heroicon-o-academic-cap');
    }

    public static function getPages(): array
    {
        return ['index' => ManageTeachers::route('/')];
    }
}
